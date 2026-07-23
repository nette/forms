<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Repeater min/max validation.
 */

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
});


test('submitting fewer items than minimum adds a single count error', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'John'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name')->setRequired();
	});
	$repeater->setBounds(min: 2, max: 5);

	// submitted items are not padded with empty ones before validation,
	// so the only error is the count violation (no phantom "required" errors)
	Assert::count(1, $repeater->getComponents());
	Assert::false($form->isValid());
	Assert::same(['Please add at least 2 items.'], $form->getErrors());

	// the missing rows appear only when re-rendering the form
	$repeater->render(function ($container) {});
	Assert::count(2, $repeater->getComponents());
});


test('count within bounds passes', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'John'],
		1 => ['name' => 'Jane'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});
	$repeater->setBounds(min: 1, max: 2);

	Assert::true($form->isValid());
	Assert::same([], $form->getErrors());
});


test('count validation is skipped when form is not submitted', function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';

	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});
	$repeater->setBounds(min: 2);

	$form->validate();
	Assert::same([], $form->getErrors());
});


test('translator receives the message template', function () {
	$_POST = ['persons' => []];

	$form = new Form;
	$form->allowCrossOrigin();
	$form->setTranslator(new class implements Nette\Localization\Translator {
		public function translate($message, ...$parameters): string
		{
			return '_' . $message . '_';
		}
	});
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});
	$repeater->setBounds(min: 1);

	Assert::false($form->isValid());
	Assert::same(['_Please add at least 1 items._'], $form->getErrors());
});


test('forged submission beyond the maximum does not build unbounded containers', function () {
	$_POST = ['persons' => array_fill(0, 200, ['name' => 'x'])];

	$form = new Form;
	$form->allowCrossOrigin();
	$invocations = 0;
	$repeater = $form->addRepeater('persons', function ($container) use (&$invocations) {
		$invocations++;
		$container->addText('name');
	}, max: 3);

	// the factory runs only for a bounded number of items, the violation is reported from the raw count
	Assert::true($invocations <= 3 + 8, "factory ran {$invocations} times");
	Assert::false($form->isValid());
	Assert::same(['Please add no more than 3 items.'], $form->getErrors());
});


test('late setBounds() on a submitted form drops the containers built beyond the cap', function () {
	$_POST = ['persons' => array_fill(0, 50, ['name' => 'x'])];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});
	$repeater->setBounds(max: 3);

	Assert::true(count($repeater->getComponents()) <= 3 + 8);
	Assert::false($form->isValid());
	Assert::same(['Please add no more than 3 items.'], $form->getErrors());
});


test('min/max is exported to data attributes for client side', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});
	$repeater->setBounds(min: 1, max: 5);

	$output = $repeater->render(function ($container) {});

	Assert::contains('data-nette-repeater="persons"', $output);
	Assert::contains('data-nette-repeater-min="1"', $output);
	Assert::contains('data-nette-repeater-max="5"', $output);
});
