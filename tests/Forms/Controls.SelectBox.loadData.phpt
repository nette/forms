<?php

/**
 * Test: Nette\Forms\Controls\SelectBox.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\DateTime;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';
});


$series = [
	'red-dwarf' => 'Red Dwarf',
	'the-simpsons' => 'The Simpsons',
	0 => 'South Park',
	'' => 'Family Guy',
];


test('Select', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select', null, $series);

	Assert::true($form->isValid());
	Assert::same('red-dwarf', $input->getValue());
	Assert::same('Red Dwarf', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('Empty select', function () {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select');

	Assert::true($form->isValid());
	Assert::same(null, $input->getValue());
	Assert::same(null, $input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('Select with prompt', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select', null, $series)->setPrompt('Select series');

	Assert::true($form->isValid());
	Assert::same('red-dwarf', $input->getValue());
	Assert::same('Red Dwarf', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('Select with more visible options and no input', function () use ($series) {
	$form = new Form;
	$input = $form->addSelect('select', null, $series);
	$input->getControlPrototype()->size = 2;

	Assert::true($form->isValid());
	Assert::same(null, $input->getValue());
	Assert::same(null, $input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('Select with optgroups', function () {
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


test('Select with invalid input', function () use ($series) {
	$_POST = ['select' => 'days-of-our-lives'];

	$form = new Form;
	$input = $form->addSelect('select', null, $series);

	Assert::false($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('Select with prompt and invalid input', function () use ($series) {
	$form = new Form;
	$input = $form->addSelect('select', null, $series)->setPrompt('Select series');

	Assert::true($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('Indexed arrays', function () use ($series) {
	$_POST = ['zero' => '0'];

	$form = new Form;
	$input = $form->addSelect('zero', null, $series);

	Assert::true($form->isValid());
	Assert::same(0, $input->getValue());
	Assert::same(0, $input->getRawValue());
	Assert::same('South Park', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('empty key', function () use ($series) {
	$_POST = ['empty' => ''];

	$form = new Form;
	$input = $form->addSelect('empty', null, $series);

	Assert::true($form->isValid());
	Assert::same('', $input->getValue());
	Assert::same('Family Guy', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('missing key', function () use ($series) {
	$form = new Form;
	$input = $form->addSelect('missing', null, $series);

	Assert::false($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('disabled key', function () use ($series) {
	$_POST = ['disabled' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('disabled', null, $series)
		->setDisabled();

	Assert::true($form->isValid());
	Assert::null($input->getValue());
});


test('malformed data', function () use ($series) {
	$_POST = ['malformed' => ['']];

	$form = new Form;
	$input = $form->addSelect('malformed', null, $series);

	Assert::false($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('setItems without keys', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select')->setItems(array_keys($series), false);
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


test('setItems without keys', function () {
	$form = new Form;
	$input = $form->addSelect('select')->setItems(range(1, 5), false);
	Assert::same([1 => 1, 2, 3, 4, 5], $input->getItems());
});


test('setItems without keys with optgroups', function () {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form->addSelect('select')->setItems([
		'usa' => ['the-simpsons', 0],
		'uk' => ['red-dwarf'],
	], false);

	Assert::true($form->isValid());
	Assert::same('red-dwarf', $input->getValue());
	Assert::same('red-dwarf', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('setValue() and invalid argument', function () use ($series) {
	$form = new Form;
	$input = $form->addSelect('select', null, $series);
	$input->setValue(null);

	Assert::exception(function () use ($input) {
		$input->setValue('unknown');
	}, Nette\InvalidArgumentException::class, "Value 'unknown' is out of allowed set ['red-dwarf', 'the-simpsons', 0, ''] in field 'select'.");
});


test('object as value', function () {
	$form = new Form;
	$input = $form->addSelect('select', null, ['2013-07-05 00:00:00' => 1])
		->setValue(new DateTime('2013-07-05'));

	Assert::same('2013-07-05 00:00:00', $input->getValue());
});


test('object as item', function () {
	$form = new Form;
	$input = $form->addSelect('select')
		->setItems([
			'group' => [new DateTime('2013-07-05')],
			new DateTime('2013-07-06'),
		], false)
		->setValue('2013-07-05 00:00:00');

	Assert::equal(new DateTime('2013-07-05'), $input->getSelectedItem());
});


test('disabled one', function () use ($series) {
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

test('', function () {
	$_POST = ['select' => '1'];

	$form = new Form;
	$input = $form->addSelect('select', null, [
		1 => null,
		2 => 'Red dwarf',
	]);

	Assert::same(1, $input->getValue());
});
