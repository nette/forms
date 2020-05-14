<?php

/**
 * Test: Nette\Forms\Controls\CheckboxList.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Validator;
use Nette\Utils\DateTime;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
});


$series = [
	'red-dwarf' => 'Red Dwarf',
	'the-simpsons' => 'The Simpsons',
	0 => 'South Park',
	'' => 'Family Guy',
];


test(function () use ($series) { // empty input
	$_POST = [];

	$form = new Form;
	$input = $form->addCheckboxList('list', null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test(function () use ($series) { // compact mode
	$_POST = ['list' => 'red-dwarf,0'];

	$form = new Form;
	$input = $form->addCheckboxList('list', null, $series);

	Assert::true($form->isValid());
	Assert::same(['red-dwarf', 0], $input->getValue());
	Assert::same(['red-dwarf' => 'Red Dwarf', 0 => 'South Park'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test(function () use ($series) { // multiple selected items, zero item
	$_POST = ['multi' => ['red-dwarf', 'unknown', 0]];

	$form = new Form;
	$input = $form->addCheckboxList('multi', null, $series);

	Assert::true($form->isValid());
	Assert::same(['red-dwarf', 0], $input->getValue());
	Assert::same(['red-dwarf', 'unknown', 0], $input->getRawValue());
	Assert::same(['red-dwarf' => 'Red Dwarf', 0 => 'South Park'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test(function () use ($series) { // empty key
	$_POST = ['empty' => ['']];

	$form = new Form;
	$input = $form->addCheckboxList('empty', null, $series);

	Assert::true($form->isValid());
	Assert::same([''], $input->getValue());
	Assert::same(['' => 'Family Guy'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test(function () use ($series) { // missing key
	$form = new Form;
	$input = $form->addCheckboxList('missing', null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test(function () use ($series) { // disabled key
	$_POST = ['disabled' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addCheckboxList('disabled', null, $series)
		->setDisabled();

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
});


test(function () use ($series) { // malformed data
	$_POST = ['malformed' => [[null]]];

	$form = new Form;
	$input = $form->addCheckboxList('malformed', null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test(function () use ($series) { // validateLength
	$_POST = ['multi' => ['red-dwarf', 'unknown', 0]];

	$form = new Form;
	$input = $form->addCheckboxList('multi', null, $series);

	Assert::true(Validator::validateLength($input, 2));
	Assert::false(Validator::validateLength($input, 3));
	Assert::false(Validator::validateLength($input, [3]));
	Assert::true(Validator::validateLength($input, [0, 3]));
});


test(function () use ($series) { // validateEqual
	$_POST = ['multi' => ['red-dwarf', 'unknown', 0]];

	$form = new Form;
	$input = $form->addCheckboxList('multi', null, $series);

	Assert::true(Validator::validateEqual($input, ['red-dwarf', 0]));
	Assert::false(Validator::validateEqual($input, 'unknown'));
	Assert::false(Validator::validateEqual($input, ['unknown']));
	Assert::false(Validator::validateEqual($input, [0]));
});


test(function () use ($series) { // setValue() and invalid argument
	$form = new Form;
	$input = $form->addCheckboxList('list', null, $series);
	$input->setValue(null);

	Assert::exception(function () use ($input) {
		$input->setValue('unknown');
	}, Nette\InvalidArgumentException::class, "Value 'unknown' are out of allowed set ['red-dwarf', 'the-simpsons', 0, ''] in field 'list'.");
});


test(function () { // object as value
	$form = new Form;
	$input = $form->addCheckboxList('list', null, ['2013-07-05 00:00:00' => 1])
		->setValue([new DateTime('2013-07-05')]);

	Assert::same(['2013-07-05 00:00:00'], $input->getValue());
});


test(function () { // object as item
	$form = new Form;
	$input = $form->addCheckboxList('list')
		->setItems([new DateTime('2013-07-05')], false)
		->setValue('2013-07-05 00:00:00');

	Assert::equal(['2013-07-05 00:00:00' => new DateTime('2013-07-05')], $input->getSelectedItems());
});


test(function () use ($series) { // disabled one
	$_POST = ['list' => ['red-dwarf', 0]];

	$form = new Form;
	$input = $form->addCheckboxList('list', null, $series)
		->setDisabled(['red-dwarf']);

	Assert::same([0], $input->getValue());

	unset($form['list']);
	$input = new Nette\Forms\Controls\CheckboxList(null, $series);
	$input->setDisabled(['red-dwarf']);
	$form['list'] = $input;

	Assert::same([0], $input->getValue());
});
