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


test('empty checkbox list submission', function () use ($series) {
	$_POST = [];

	$form = new Form;
	$input = $form->addCheckboxList('list', null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test('multiple valid selections', function () use ($series) {
	$_POST = ['list' => 'red-dwarf,0'];

	$form = new Form;
	$input = $form->addCheckboxList('list', null, $series);

	Assert::true($form->isValid());
	Assert::same(['red-dwarf', 0], $input->getValue());
	Assert::same(['red-dwarf' => 'Red Dwarf', 0 => 'South Park'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test('filtering invalid selections', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf', 'unknown', '0']];

	$form = new Form;
	$input = $form->addCheckboxList('multi', null, $series);

	Assert::true($form->isValid());
	Assert::same(['red-dwarf', 0], $input->getValue());
	Assert::same(['red-dwarf', 'unknown', 0], $input->getRawValue());
	Assert::same(['red-dwarf' => 'Red Dwarf', 0 => 'South Park'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test('empty string as valid selection', function () use ($series) {
	$_POST = ['empty' => ['']];

	$form = new Form;
	$input = $form->addCheckboxList('empty', null, $series);

	Assert::true($form->isValid());
	Assert::same([''], $input->getValue());
	Assert::same(['' => 'Family Guy'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test('missing checkbox list data', function () use ($series) {
	$form = new Form;
	$input = $form->addCheckboxList('missing', null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test('disabled checkbox list ignores input', function () use ($series) {
	$_POST = ['disabled' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addCheckboxList('disabled', null, $series)
		->setDisabled();

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
});


test('nested malformed array input', function () use ($series) {
	$_POST = ['malformed' => [['']]];

	$form = new Form;
	$input = $form->addCheckboxList('malformed', null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test('selection length validation', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf', 'unknown', '0']];

	$form = new Form;
	$input = $form->addCheckboxList('multi', null, $series);

	Assert::true(Validator::validateLength($input, 2));
	Assert::false(Validator::validateLength($input, 3));
	Assert::false(Validator::validateLength($input, [3]));
	Assert::true(Validator::validateLength($input, [0, 3]));
});


test('equality validation with mixed values', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf', 'unknown', '0']];

	$form = new Form;
	$input = $form->addCheckboxList('multi', null, $series);

	Assert::true(Validator::validateEqual($input, ['red-dwarf', 0]));
	Assert::false(Validator::validateEqual($input, 'unknown'));
	Assert::false(Validator::validateEqual($input, ['unknown']));
	Assert::false(Validator::validateEqual($input, [0]));
	Assert::false(Validator::validateEqual($input, []));
});


test('empty list equality checks', function () use ($series) {
	$_POST = [];

	$form = new Form;
	$input = $form->addCheckboxList('multi', null, $series);

	Assert::false(Validator::validateEqual($input, ['red-dwarf', 0]));
	Assert::false(Validator::validateEqual($input, 'unknown'));
	Assert::false(Validator::validateEqual($input, ['unknown']));
	Assert::false(Validator::validateEqual($input, [0]));
	Assert::false(Validator::validateEqual($input, []));
});


testException('invalid selection exception', function () use ($series) {
	$form = new Form;
	$input = $form->addCheckboxList('list', null, $series);
	$input->setValue(null);
	$input->setValue('unknown');
}, Nette\InvalidArgumentException::class, "Value 'unknown' are out of allowed set ['red-dwarf', 'the-simpsons', 0, ''] in field 'list'.");


test('dateTime object as selection key', function () {
	$form = new Form;
	$input = $form->addCheckboxList('list', null, ['2013-07-05 00:00:00' => 1])
		->setValue([new DateTime('2013-07-05')]);

	Assert::same(['2013-07-05 00:00:00'], $input->getValue());
});


test('dateTime items without keys', function () {
	$form = new Form;
	$input = $form->addCheckboxList('list')
		->setItems([new DateTime('2013-07-05')], useKeys: false)
		->setValue('2013-07-05 00:00:00');

	Assert::equal(['2013-07-05 00:00:00' => new DateTime('2013-07-05')], $input->getSelectedItems());
});


test('disabled item filtering', function () use ($series) {
	$_POST = ['list' => ['red-dwarf', '0']];

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
