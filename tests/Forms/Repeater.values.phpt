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


test('form reset makes the repeater as on a form that was not submitted', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'John', 'emails' => [['a' => 'x'], ['a' => 'y']]],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name')->setDefaultValue('?');
		$container->addRepeater('emails', function ($email) {
			$email->addText('a');
		}, default: 1);
	}, min: 2, default: 3);

	Assert::false($form->isValid());
	Assert::count(1, $repeater->getComponents());

	$form->reset();
	Assert::count(3, $repeater->getComponents());
	Assert::same('?', $repeater['0']['name']->getValue());
	Assert::count(1, $repeater['0']['emails']->getComponents());

	$form->validate();
	Assert::same([], $form->getErrors());
	Assert::true($form->isValid());
});


test('values with non-sequential keys are treated as a list', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$form->setDefaults(['persons' => [4 => ['name' => 'John'], 9 => ['name' => 'Jane']]]);
	Assert::same([['name' => 'John'], ['name' => 'Jane']], $repeater->getValues('array'));

	$repeater->setValues(new ArrayIterator(['a' => ['name' => 'Bob']]), erase: true);
	Assert::same([['name' => 'Bob']], $repeater->getValues('array'));
});


test('getValues() returns ArrayHash of ArrayHash items, or arrays for array', function () {
	$_POST = ['persons' => [['name' => 'John'], ['name' => 'Jane']]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$values = $repeater->getValues();
	Assert::type(Nette\Utils\ArrayHash::class, $values);
	Assert::type(Nette\Utils\ArrayHash::class, $values[1]);
	Assert::same('Jane', $values[1]->name);
	Assert::same([['name' => 'John'], ['name' => 'Jane']], $repeater->getValues('array'));
	Assert::same(['persons' => [['name' => 'John'], ['name' => 'Jane']]], $form->getValues('array'));
});


test('with a mapped type, getValues() returns a list of objects even for array and in a validation scope', function () {
	$_POST = ['persons' => [['name' => 'John', 'email' => 'j@x'], ['name' => 'Jane', 'email' => 'e@x']], 'note' => 'n', 'send' => ''];

	$form = new Form;
	$form->allowCrossOrigin();
	$form->addText('note');
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addText('email');
	});
	$repeater->setMappedType(RepeaterPersonDto::class);
	$form->addSubmit('send')->setValidationScope([$repeater['1']]);

	$values = $form->getValues('array');
	Assert::same(['persons'], array_keys($values));
	Assert::type('list', $values['persons']);
	Assert::equal([new RepeaterPersonDto, (function () {
		$dto = new RepeaterPersonDto;
		$dto->name = 'Jane';
		$dto->email = 'e@x';
		return $dto;
	})()], $values['persons']);
});
