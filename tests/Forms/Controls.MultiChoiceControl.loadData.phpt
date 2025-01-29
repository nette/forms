<?php

/**
 * Test: Nette\Forms\Controls\MultiChoiceControl.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Validator;
use Nette\Utils\DateTime;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MultiChoiceControl extends Nette\Forms\Controls\MultiChoiceControl
{
}


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
	ob_start();
	Form::initialize(true);
});


$series = [
	'red-dwarf' => 'Red Dwarf',
	'the-simpsons' => 'The Simpsons',
	0 => 'South Park',
	'' => 'Family Guy',
];


test('single value treated as empty', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test('multiple selections with invalid entries', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf', 'unknown', '0']];

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl(null, $series);

	Assert::true($form->isValid());
	Assert::same(['red-dwarf', 0], $input->getValue());
	Assert::same(['red-dwarf', 'unknown', 0], $input->getRawValue());
	Assert::same(['red-dwarf' => 'Red Dwarf', 0 => 'South Park'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test('empty string as valid selection', function () use ($series) {
	$_POST = ['empty' => ['']];

	$form = new Form;
	$input = $form['empty'] = new MultiChoiceControl(null, $series);

	Assert::true($form->isValid());
	Assert::same([''], $input->getValue());
	Assert::same(['' => 'Family Guy'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test('missing multi-choice input', function () use ($series) {
	$form = new Form;
	$input = $form['missing'] = new MultiChoiceControl(null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test('disabled multi-choice ignores input', function () use ($series) {
	$_POST = ['disabled' => 'red-dwarf'];

	$form = new Form;
	$input = $form['disabled'] = new MultiChoiceControl(null, $series);
	$input->setDisabled();

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
});


test('malformed array input', function () use ($series) {
	$_POST = ['malformed' => [['']]];

	$form = new Form;
	$input = $form['malformed'] = new MultiChoiceControl(null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test('keys as items without labels', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf']];

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl;
	$input->setItems(array_keys($series), useKeys: false);
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


test('selection length validation', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf', 'unknown', '0']];

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl(null, $series);

	Assert::true(Validator::validateLength($input, 2));
	Assert::false(Validator::validateLength($input, 3));
	Assert::false(Validator::validateLength($input, [3]));
	Assert::true(Validator::validateLength($input, [0, 3]));
});


test('equality validation with mixed values', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf', 'unknown', '0']];

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl(null, $series);

	Assert::true(Validator::validateEqual($input, ['red-dwarf', 0]));
	Assert::false(Validator::validateEqual($input, 'unknown'));
	Assert::false(Validator::validateEqual($input, ['unknown']));
	Assert::false(Validator::validateEqual($input, [0]));
	Assert::false(Validator::validateEqual($input, []));
});


test('empty submission validation', function () use ($series) {
	$_POST = [];

	$form = new Form;
	$input = $form['multi'] = new MultiChoiceControl(null, $series);

	Assert::false(Validator::validateEqual($input, ['red-dwarf', 0]));
	Assert::false(Validator::validateEqual($input, 'unknown'));
	Assert::false(Validator::validateEqual($input, ['unknown']));
	Assert::false(Validator::validateEqual($input, [0]));
	Assert::false(Validator::validateEqual($input, []));
});


test('exceptions for invalid values', function () use ($series) {
	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(null, $series);
	$input->setValue(null);

	Assert::exception(
		fn() => $input->setValue('unknown'),
		Nette\InvalidArgumentException::class,
		"Value 'unknown' are out of allowed set ['red-dwarf', 'the-simpsons', 0, ''] in field 'select'.",
	);

	Assert::exception(
		fn() => $input->setValue(new stdClass),
		Nette\InvalidArgumentException::class,
		"Value must be array or null, stdClass given in field 'select'.",
	);

	Assert::exception(
		fn() => $input->setValue([new stdClass]),
		Nette\InvalidArgumentException::class,
		"Values must be scalar, stdClass given in field 'select'.",
	);
});


test('invalid values ignored with checkDefaultValue', function () use ($series) {
	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(null, $series);
	$input->checkDefaultValue(false);
	$input->setValue('unknown');
	Assert::same([], $input->getValue());

	Assert::exception(
		fn() => $input->setValue(new stdClass),
		Nette\InvalidArgumentException::class,
		"Value must be array or null, stdClass given in field 'select'.",
	);

	Assert::exception(
		fn() => $input->setValue([new stdClass]),
		Nette\InvalidArgumentException::class,
		"Values must be scalar, stdClass given in field 'select'.",
	);
});


test('dateTime object as value', function () {
	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(null, ['2013-07-05 00:00:00' => 1]);
	$input->setValue([new DateTime('2013-07-05')]);

	Assert::same(['2013-07-05 00:00:00'], $input->getValue());
});


test('disabled items ignored in multi-choice', function () use ($series) {
	$_POST = ['select' => ['red-dwarf', '0']];

	$form = new Form;
	$input = $form['select'] = new MultiChoiceControl(null, $series);
	$input->setDisabled(['red-dwarf']);

	Assert::same([0], $input->getValue());

	unset($form['select']);
	$input = new Nette\Forms\Controls\MultiSelectBox(null, $series);
	$input->setDisabled(['red-dwarf']);
	$form['select'] = $input;

	Assert::same([0], $input->getValue());
});
