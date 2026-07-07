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
	Assert::false($form->isValid());
	Assert::count(1, $repeater->getComponents());
	Assert::same(['Please add at least 2 items.'], $form->getErrors());
	Assert::same(['Please add at least 2 items.'], $repeater->getOwnErrors());
	Assert::same(['Please add at least 2 items.'], $repeater->getErrors());
});


test('rendering offers the missing rows without making them submitted values', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'John'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name')->setDefaultValue('?');
	}, min: 3);

	Assert::same([['name' => 'John']], $repeater->getUntrustedValues('array'));

	$output = $repeater->render(function ($container) {
		echo $container['name']->getControl();
	});

	Assert::contains(
		'<div data-repeater-index="1"><input type="text" name="persons[1][name]" id="frm-persons-1-name" value="?"></div>',
		$output,
	);
	Assert::contains('data-repeater-index="2"', $output);
	Assert::count(1, $repeater->getComponents());
	Assert::same([['name' => 'John']], $repeater->getUntrustedValues('array'));
	Assert::false($form->isValid());
	Assert::same(['Please add at least 3 items.'], $form->getErrors());
});


test('onValidate handlers see the count error', function () {
	$_POST = ['persons' => []];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	}, min: 1);

	$seen = null;
	$repeater->onValidate[] = function ($repeater) use (&$seen) {
		$seen = $repeater->getErrors();
	};

	$form->validate();
	Assert::same(['Please add at least 1 items.'], $seen);
});


test('count errors of nested repeaters stay with the repeater they concern', function () {
	$_POST = ['persons' => [
		['emails' => [['a' => 'x']]],
		['emails' => []],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$persons = $form->addRepeater('persons', function ($person) {
		$person->addRepeater('emails', function ($email) {
			$email->addText('a');
		}, min: 1);
	});

	Assert::false($form->isValid());
	Assert::same(['Please add at least 1 items.'], $form->getErrors());
	Assert::same([], $persons['0']['emails']->getErrors());
	Assert::same(['Please add at least 1 items.'], $persons['1']['emails']->getOwnErrors());
	Assert::same([], $persons->getOwnErrors());
	Assert::same(['Please add at least 1 items.'], $persons->getErrors());
});


test('a repeater outside the validation scope is not counted', function () {
	$_POST = ['persons' => [], 'send' => ''];

	$form = new Form;
	$form->allowCrossOrigin();
	$form->addText('x');
	$form->addRepeater('persons', function ($container) {
		$container->addText('name');
	}, min: 1);
	$form->addSubmit('send')->setValidationScope([$form['x']]);

	Assert::true($form->isValid());
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
	Assert::true($invocations <= 3 + 8 + 1, "factory ran {$invocations} times"); // items and the template
	Assert::false($form->isValid());
	Assert::same(['Please add no more than 3 items.'], $form->getErrors());
});


test('bounds set after addRepeater() cap the built items too', function () {
	$_POST = ['persons' => array_fill(0, 50, ['name' => 'x'])];

	$form = new Form;
	$form->allowCrossOrigin();
	$invocations = 0;
	$repeater = $form->addRepeater('persons', function ($container) use (&$invocations) {
		$invocations++;
		$container->addText('name');
	});
	$repeater->setBounds(max: 3);

	Assert::false($form->isValid());
	Assert::same(['Please add no more than 3 items.'], $form->getErrors());
	Assert::count(3 + 8, $repeater->getComponents());
	Assert::same(3 + 8 + 1, $invocations); // items and the template
});


test('a repeater without maximum has its own limit', function () {
	$_POST = ['persons' => array_fill(0, 1200, ['name' => 'x'])];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	Assert::false($form->isValid());
	Assert::same(['Please add no more than 1000 items.'], $form->getErrors());
	Assert::count(1000 + 8, $repeater->getComponents());
	Assert::null($repeater->getContainerAttributes()['data-nette-repeater-max']);
});


test('late setBounds() on a loaded submission drops the containers built beyond the cap', function () {
	$_POST = ['persons' => array_fill(0, 50, ['name' => 'x'])];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});
	Assert::count(50, $repeater->getValues('array'));

	$repeater->setBounds(max: 3);
	Assert::count(3 + 8, $repeater->getComponents());
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
