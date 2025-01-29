<?php

/**
 * Test: Nette\Forms\Controls\MultiSelectBox.
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


test('selection within grouped items', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf']];

	$form = new Form;
	$input = $form->addMultiSelect('multi', null, [
		'usa' => [
			'the-simpsons' => 'The Simpsons',
			0 => 'South Park',
		],
		'uk' => [
			'red-dwarf' => 'Red Dwarf',
		],
	]);

	Assert::true($form->isValid());
	Assert::same(['red-dwarf'], $input->getValue());
	Assert::same(['red-dwarf' => 'Red Dwarf'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test('single value treated as empty', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addMultiSelect('select', null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test('multiple selections with invalid entries', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf', 'unknown', '0']];

	$form = new Form;
	$input = $form->addMultiSelect('multi', null, $series);

	Assert::true($form->isValid());
	Assert::same(['red-dwarf', 0], $input->getValue());
	Assert::same(['red-dwarf', 'unknown', 0], $input->getRawValue());
	Assert::same(['red-dwarf' => 'Red Dwarf', 0 => 'South Park'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test('empty string as valid selection', function () use ($series) {
	$_POST = ['empty' => ['']];

	$form = new Form;
	$input = $form->addMultiSelect('empty', null, $series);

	Assert::true($form->isValid());
	Assert::same([''], $input->getValue());
	Assert::same(['' => 'Family Guy'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


test('missing multi-select input', function () use ($series) {
	$form = new Form;
	$input = $form->addMultiSelect('missing', null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test('disabled multi-select ignores input', function () use ($series) {
	$_POST = ['disabled' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addMultiSelect('disabled', null, $series)
		->setDisabled();

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
});


test('malformed array input', function () use ($series) {
	$_POST = ['malformed' => [['']]];

	$form = new Form;
	$input = $form->addMultiSelect('malformed', null, $series);

	Assert::true($form->isValid());
	Assert::same([], $input->getValue());
	Assert::same([], $input->getSelectedItems());
	Assert::false($input->isFilled());
});


test('selection length validation', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf', 'unknown', '0']];

	$form = new Form;
	$input = $form->addMultiSelect('multi', null, $series);

	Assert::true(Validator::validateLength($input, 2));
	Assert::false(Validator::validateLength($input, 3));
	Assert::false(Validator::validateLength($input, [3]));
	Assert::true(Validator::validateLength($input, [0, 3]));
});


test('equality validation with mixed values', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf', 'unknown', '0']];

	$form = new Form;
	$input = $form->addMultiSelect('multi', null, $series);

	Assert::true(Validator::validateEqual($input, ['red-dwarf', 0]));
	Assert::false(Validator::validateEqual($input, 'unknown'));
	Assert::false(Validator::validateEqual($input, ['unknown']));
	Assert::false(Validator::validateEqual($input, [0]));
	Assert::false(Validator::validateEqual($input, []));
});


test('empty submission validation', function () use ($series) {
	$_POST = [];

	$form = new Form;
	$input = $form->addMultiSelect('multi', null, $series);

	Assert::false(Validator::validateEqual($input, ['red-dwarf', 0]));
	Assert::false(Validator::validateEqual($input, 'unknown'));
	Assert::false(Validator::validateEqual($input, ['unknown']));
	Assert::false(Validator::validateEqual($input, [0]));
	Assert::false(Validator::validateEqual($input, []));
});


test('keys as items without labels', function () use ($series) {
	$_POST = ['multi' => ['red-dwarf']];

	$form = new Form;
	$input = $form->addMultiSelect('multi')->setItems(array_keys($series), useKeys: false);
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


test('numeric keys handling', function () use ($series) {
	$form = new Form;
	$input = $form->addMultiSelect('select')->setItems(range(1, 5), useKeys: false);
	Assert::same([1 => 1, 2, 3, 4, 5], $input->getItems());
});


test('grouped items without keys', function () {
	$_POST = ['multi' => ['red-dwarf']];

	$form = new Form;
	$input = $form->addMultiSelect('multi')->setItems([
		'usa' => ['the-simpsons', 0],
		'uk' => ['red-dwarf'],
	], useKeys: false);

	Assert::true($form->isValid());
	Assert::same(['red-dwarf'], $input->getValue());
	Assert::same(['red-dwarf' => 'red-dwarf'], $input->getSelectedItems());
	Assert::true($input->isFilled());
});


testException('exception on invalid value', function () use ($series) {
	$form = new Form;
	$input = $form->addMultiSelect('select', null, $series);
	$input->setValue('unknown');
}, Nette\InvalidArgumentException::class, "Value 'unknown' are out of allowed set ['red-dwarf', 'the-simpsons', 0, ''] in field 'select'.");


test('dateTime object as value', function () {
	$form = new Form;
	$input = $form->addMultiSelect('select', null, ['2013-07-05 00:00:00' => 1])
		->setValue([new DateTime('2013-07-05')]);

	Assert::same(['2013-07-05 00:00:00'], $input->getValue());
});


test('dateTime items without keys', function () {
	$form = new Form;
	$input = $form->addMultiSelect('select')
		->setItems([
			'group' => [new DateTime('2013-07-05')],
			new DateTime('2013-07-06'),
		], useKeys: false)
		->setValue('2013-07-05 00:00:00');

	Assert::equal(['2013-07-05 00:00:00' => new DateTime('2013-07-05')], $input->getSelectedItems());
});


test('disabled items ignored in multi-select', function () use ($series) {
	$_POST = ['select' => ['red-dwarf', '0']];

	$form = new Form;
	$input = $form->addMultiSelect('select', null, $series)
		->setDisabled(['red-dwarf']);

	Assert::same([0], $input->getValue());

	unset($form['select']);
	$input = new Nette\Forms\Controls\MultiSelectBox(null, $series);
	$input->setDisabled(['red-dwarf']);
	$form['select'] = $input;

	Assert::same([0], $input->getValue());
});
