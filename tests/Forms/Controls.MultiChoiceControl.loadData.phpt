<?php

/**
 * Test: Nette\Forms\Controls\MultiChoiceControl.
 */

use Nette\Forms\Form;
use Nette\Forms\Validator;
use Nette\Utils\DateTime;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MultiChoiceControl extends Nette\Forms\Controls\MultiChoiceControl
{}


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


test(function () use ($series) { // invalid input
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(NULL, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test(function () use ($series) { // multiple selected items, zero item
	$_POST = ['multi' => ['red-dwarf', 'unknown', 0]];

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl(NULL, $series);

	Assert::true($form->isValid());
	Assert::same(['red-dwarf', 0], $input->getValue());
	Assert::same(['red-dwarf', 'unknown', 0], $input->getRawValue());
	Assert::same(['red-dwarf' => 'Red Dwarf', 0 => 'South Park'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test(function () use ($series) { // empty key
	$_POST = ['empty' => ['']];

	$form = new Form;
	$input = $form['empty'] = new MultiChoiceControl(NULL, $series);

	Assert::true($form->isValid());
	Assert::same([''], $input->getValue());
	Assert::same(['' => 'Family Guy'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test(function () use ($series) { // missing key
	$form = new Form;
	$input = $form['missing'] = new MultiChoiceControl(NULL, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test(function () use ($series) { // disabled key
	$_POST = ['disabled' => 'red-dwarf'];

	$form = new Form;
	$input = $form['disabled'] = new MultiChoiceControl(NULL, $series);
	$input->setDisabled();

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
});


test(function () use ($series) { // malformed data
	$_POST = ['malformed' => [[NULL]]];

	$form = new Form;
	$input = $form['malformed'] = new MultiChoiceControl(NULL, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test(function () use ($series) { // setItems without keys
	$_POST = ['multi' => ['red-dwarf']];

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl;
	$input->setItems(array_keys($series), FALSE);
	Assert::same([
		'red-dwarf' => 'red-dwarf',
		'the-simpsons' => 'the-simpsons',
		0 => 0,
		'' => '',
	], $input->getItems());

	Assert::true($form->isValid());
	Assert::same(['red-dwarf'], $input->getValue());
	Assert::same(['red-dwarf' => 'red-dwarf'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test(function () use ($series) { // validateLength
	$_POST = ['multi' => ['red-dwarf', 'unknown', 0]];

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl(NULL, $series);

	Assert::true(Validator::validateLength($input, 2));
	Assert::false(Validator::validateLength($input, 3));
	Assert::false(Validator::validateLength($input, [3]));
	Assert::true(Validator::validateLength($input, [0, 3]));
});


test(function () use ($series) { // validateEqual
	$_POST = ['multi' => ['red-dwarf', 'unknown', 0]];

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl(NULL, $series);

	Assert::true(Validator::validateEqual($input, ['red-dwarf', 0]));
	Assert::false(Validator::validateEqual($input, 'unknown'));
	Assert::false(Validator::validateEqual($input, ['unknown']));
	Assert::false(Validator::validateEqual($input, [0]));
});


test(function () use ($series) { // setValue() and invalid argument
	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(NULL, $series);
	$input->setValue(NULL);

	Assert::exception(function () use ($input) {
		$input->setValue('unknown');
	}, Nette\InvalidArgumentException::class, "Value 'unknown' are out of allowed set ['red-dwarf', 'the-simpsons', 0, ''] in field 'select'.");

	Assert::exception(function () use ($input) {
		$input->setValue(new stdClass);
	}, Nette\InvalidArgumentException::class, "Value must be array or NULL, object given in field 'select'.");

	Assert::exception(function () use ($input) {
		$input->setValue([new stdClass]);
	}, Nette\InvalidArgumentException::class, "Values must be scalar, object given in field 'select'.");
});


test(function () use ($series) { // setValue() and disabled $checkAllowedValues
	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(NULL, $series);
	$input->checkAllowedValues = FALSE;
	$input->setValue('unknown');
	Assert::same([], $input->getValue());

	Assert::exception(function () use ($input) {
		$input->setValue(new stdClass);
	}, Nette\InvalidArgumentException::class, "Value must be array or NULL, object given in field 'select'.");

	Assert::exception(function () use ($input) {
		$input->setValue([new stdClass]);
	}, Nette\InvalidArgumentException::class, "Values must be scalar, object given in field 'select'.");
});


test(function () { // object as value
	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(NULL, ['2013-07-05 00:00:00' => 1]);
	$input->setValue([new DateTime('2013-07-05')]);

	Assert::same(['2013-07-05 00:00:00'], $input->getValue());
});


test(function () use ($series) { // disabled one
	$_POST = ['select' => ['red-dwarf', 0]];

	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(NULL, $series);
	$input->setDisabled(['red-dwarf']);

	Assert::same([0], $input->getValue());

	unset($form['select']);
	$input = new Nette\Forms\Controls\MultiSelectBox(NULL, $series);
	$input->setDisabled(['red-dwarf']);
	$form['select'] = $input;

	Assert::same([0], $input->getValue());
});
