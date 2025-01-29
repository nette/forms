<?php

/**
 * Test: Nette\Forms\Controls\SelectBox.
 */

declare(strict_types=1);

use Nette\Forms\Form;
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


test('valid select box selection', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select', null, $series);

	Assert::true($form->isValid());
	Assert::same('red-dwarf', $input->getValue());
	Assert::same('Red Dwarf', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('no items handling', function () {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select');

	Assert::true($form->isValid());
	Assert::same(null, $input->getValue());
	Assert::same(null, $input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('select box with prompt', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select', null, $series)->setPrompt('Select series');

	Assert::true($form->isValid());
	Assert::same('red-dwarf', $input->getValue());
	Assert::same('Red Dwarf', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('control prototype modification', function () use ($series) {
	$form = new Form;
	$input = $form->addSelect('select', null, $series);
	$input->getControlPrototype()->size = 2;

	Assert::true($form->isValid());
	Assert::same(null, $input->getValue());
	Assert::same(null, $input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('grouped items selection', function () {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select', null, [
		'usa' => [
			'the-simpsons' => 'The Simpsons',
			0 => 'South Park',
		],
		'uk' => [
			'red-dwarf' => 'Red Dwarf',
		],
	]);

	Assert::true($form->isValid());
	Assert::same('red-dwarf', $input->getValue());
	Assert::same('Red Dwarf', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('invalid selection handling', function () use ($series) {
	$_POST = ['select' => 'days-of-our-lives'];

	$form = new Form;
	$input = $form->addSelect('select', null, $series);

	Assert::false($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('unselected prompt handling', function () use ($series) {
	$form = new Form;
	$input = $form->addSelect('select', null, $series)->setPrompt('Select series');

	Assert::true($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('zero value selection', function () use ($series) {
	$_POST = ['zero' => '0'];

	$form = new Form;
	$input = $form->addSelect('zero', null, $series);

	Assert::true($form->isValid());
	Assert::same(0, $input->getValue());
	Assert::same(0, $input->getRawValue());
	Assert::same('South Park', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('empty string selection', function () use ($series) {
	$_POST = ['empty' => ''];

	$form = new Form;
	$input = $form->addSelect('empty', null, $series);

	Assert::true($form->isValid());
	Assert::same('', $input->getValue());
	Assert::same('Family Guy', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('missing data handling', function () use ($series) {
	$form = new Form;
	$input = $form->addSelect('missing', null, $series);

	Assert::false($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('disabled select box', function () use ($series) {
	$_POST = ['disabled' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('disabled', null, $series)
		->setDisabled();

	Assert::true($form->isValid());
	Assert::null($input->getValue());
});


test('malformed select data', function () use ($series) {
	$_POST = ['malformed' => ['']];

	$form = new Form;
	$input = $form->addSelect('malformed', null, $series);

	Assert::false($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('items without keys', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select')->setItems(array_keys($series), useKeys: false);
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


test('numeric range items', function () {
	$form = new Form;
	$input = $form->addSelect('select')->setItems(range(1, 5), useKeys: false);
	Assert::same([1 => 1, 2, 3, 4, 5], $input->getItems());
});


test('grouped items without keys', function () {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select')->setItems([
		'usa' => ['the-simpsons', 0],
		'uk' => ['red-dwarf'],
	], useKeys: false);

	Assert::true($form->isValid());
	Assert::same('red-dwarf', $input->getValue());
	Assert::same('red-dwarf', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


testException('invalid value exception', function () use ($series) {
	$form = new Form;
	$input = $form->addSelect('select', null, $series);
	$input->setValue('unknown');
}, Nette\InvalidArgumentException::class, "Value 'unknown' is out of allowed set ['red-dwarf', 'the-simpsons', 0, ''] in field 'select'.");


test('dateTime value handling', function () {
	$form = new Form;
	$input = $form->addSelect('select', null, ['2013-07-05 00:00:00' => 1])
		->setValue(new DateTime('2013-07-05'));

	Assert::same('2013-07-05 00:00:00', $input->getValue());
});


test('dateTime items in groups', function () {
	$form = new Form;
	$input = $form->addSelect('select')
		->setItems([
			'group' => [new DateTime('2013-07-05')],
			new DateTime('2013-07-06'),
		], useKeys: false)
		->setValue('2013-07-05 00:00:00');

	Assert::equal(new DateTime('2013-07-05'), $input->getSelectedItem());
});


test('disabled options handling', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select', null, $series)
		->setDisabled(['red-dwarf']);

	Assert::null($input->getValue());

	unset($form['select']);
	$input = new Nette\Forms\Controls\SelectBox(null, $series);
	$input->setDisabled(['red-dwarf']);
	$form['select'] = $input;

	Assert::null($input->getValue());
});

test('null item caption handling', function () {
	$_POST = ['select' => '1'];

	$form = new Form;
	$input = $form->addSelect('select', null, [
		1 => null,
		2 => 'Red dwarf',
	]);

	Assert::same(1, $input->getValue());
});
