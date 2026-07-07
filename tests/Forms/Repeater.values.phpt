<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Repeater values, mapping and reset.
 */

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	Form::initialize(true);
});


class RepeaterPersonDto
{
	public string $name;
	public string $email;
}


test('items are mapped to objects via setMappedType()', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'John', 'email' => 'john@example.com'],
		1 => ['name' => 'Jane', 'email' => 'jane@example.com'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addText('email');
	});
	$repeater->setMappedType(RepeaterPersonDto::class);

	Assert::true($form->isValid());
	$values = $repeater->getValues();
	Assert::type('array', $values);
	Assert::count(2, $values);
	Assert::type(RepeaterPersonDto::class, $values[0]);
	Assert::same('John', $values[0]->name);
	Assert::same('jane@example.com', $values[1]->email);

	// mapping works also when values are read from the whole form
	$formValues = $form->getValues('array');
	Assert::type(RepeaterPersonDto::class, $formValues['persons'][0]);
});


test('without mapped type items are returned as before', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'John', 'email' => 'j@example.com'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addText('email');
	});

	Assert::true($form->isValid());
	Assert::same([
		['name' => 'John', 'email' => 'j@example.com'],
	], $repeater->getValues('array'));
});


test('setValues with erase removes surplus items', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->setValues([
		['name' => 'John'],
		['name' => 'Jane'],
		['name' => 'Bob'],
	]);
	Assert::count(3, $repeater->getComponents());

	$repeater->setValues([
		['name' => 'Alice'],
	], erase: true);

	Assert::count(1, $repeater->getComponents());
	Assert::same([['name' => 'Alice']], $repeater->getValues('array'));
});


test('form reset removes submitted items', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'John'],
		1 => ['name' => 'Jane'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	Assert::truthy($form->isSubmitted());
	Assert::count(2, $repeater->getComponents());

	$form->reset();
	Assert::count(0, $repeater->getComponents());
});
