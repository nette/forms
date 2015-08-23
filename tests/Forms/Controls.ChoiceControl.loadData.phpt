<?php

/**
 * Test: Nette\Forms\Controls\ChoiceControl.
 */

use Nette\Forms\Form;
use Nette\Utils\DateTime;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class ChoiceControl extends Nette\Forms\Controls\ChoiceControl
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


test(function () use ($series) { // Select
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL, $series);

	Assert::true($form->isValid());
	Assert::same('red-dwarf', $input->getValue());
	Assert::same('Red Dwarf', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test(function () use ($series) { // Select with invalid input
	$_POST = ['select' => 'days-of-our-lives'];

	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL, $series);

	Assert::true($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test(function () use ($series) { // Indexed arrays
	$_POST = ['zero' => 0];

	$form = new Form;
	$input = $form['zero'] = new ChoiceControl(NULL, $series);

	Assert::true($form->isValid());
	Assert::same(0, $input->getValue());
	Assert::same(0, $input->getRawValue());
	Assert::same('South Park', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test(function () use ($series) { // empty key
	$_POST = ['empty' => ''];

	$form = new Form;
	$input = $form['empty'] = new ChoiceControl(NULL, $series);

	Assert::true($form->isValid());
	Assert::same('', $input->getValue());
	Assert::same('Family Guy', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test(function () use ($series) { // missing key
	$form = new Form;
	$input = $form['missing'] = new ChoiceControl(NULL, $series);

	Assert::true($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test(function () use ($series) { // disabled key
	$_POST = ['disabled' => 'red-dwarf'];

	$form = new Form;
	$input = $form['disabled'] = new ChoiceControl(NULL, $series);
	$input->setDisabled();

	Assert::true($form->isValid());
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test(function () use ($series) { // malformed data
	$_POST = ['malformed' => [NULL]];

	$form = new Form;
	$input = $form['malformed'] = new ChoiceControl(NULL, $series);

	Assert::true($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test(function () use ($series) { // setItems without keys
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form['select'] = new ChoiceControl;
	$input->setItems(array_keys($series), FALSE);
	Assert::same([
		'red-dwarf' => 'red-dwarf',
		'the-simpsons' => 'the-simpsons',
		0 => 0,
		'' => '',
	], $input->getItems());

	Assert::true($form->isValid());
	Assert::same('red-dwarf', $input->getValue());
	Assert::same('red-dwarf', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test(function () use ($series) { // setValue() and invalid argument
	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL, $series);
	$input->setValue(NULL);

	Assert::exception(function () use ($input) {
		$input->setValue('unknown');
	}, Nette\InvalidArgumentException::class, "Value 'unknown' is out of allowed set ['red-dwarf', 'the-simpsons', 0, ''] in field 'select'.");
});


test(function () use ($series) { // setValue() and disabled $checkAllowedValues
	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL, $series);
	$input->checkAllowedValues = FALSE;
	$input->setValue('unknown');
	Assert::null($input->getValue());
});


test(function () { // object as value
	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL, ['2013-07-05 00:00:00' => 1]);
	$input->setValue(new DateTime('2013-07-05'));

	Assert::same('2013-07-05 00:00:00', $input->getValue());
});


test(function () { // object as item
	$form = new Form;
	$input = $form['select'] = new ChoiceControl;
	$input->setItems([new DateTime('2013-07-05')], FALSE)
		->setValue(new DateTime('2013-07-05'));

	Assert::same('2013-07-05 00:00:00', $input->getValue());
});


test(function () use ($series) { // disabled one
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL, $series);
	$input->setDisabled(['red-dwarf']);

	Assert::null($input->getValue());

	unset($form['select']);
	$input = new ChoiceControl(NULL, $series);
	$input->setDisabled(['red-dwarf']);
	$form['select'] = $input;

	Assert::null($input->getValue());
});

test(function () {
	$_POST = ['select' => 1];

	$form = new Form;
	$input = $form['select'] = new ChoiceControl(NULL);
	$input->setItems([
		1 => NULL,
		2 => 'Red dwarf',
	]);

	Assert::same(1, $input->getValue());
});
