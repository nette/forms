<?php

/**
 * Test: Nette\Forms\Controls\ChoiceControl.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\DateTime;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class ChoiceControl extends Nette\Forms\Controls\ChoiceControl
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


test('valid selection handling', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form['select'] = new ChoiceControl(null, $series);

	Assert::true($form->isValid());
	Assert::same('red-dwarf', $input->getValue());
	Assert::same('Red Dwarf', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('invalid selection ignored', function () use ($series) {
	$_POST = ['select' => 'days-of-our-lives'];

	$form = new Form;
	$input = $form['select'] = new ChoiceControl(null, $series);

	Assert::true($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('zero value as valid key', function () use ($series) {
	$_POST = ['zero' => '0'];

	$form = new Form;
	$input = $form['zero'] = new ChoiceControl(null, $series);

	Assert::true($form->isValid());
	Assert::same(0, $input->getValue());
	Assert::same(0, $input->getRawValue());
	Assert::same('South Park', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('empty string as valid key', function () use ($series) {
	$_POST = ['empty' => ''];

	$form = new Form;
	$input = $form['empty'] = new ChoiceControl(null, $series);

	Assert::true($form->isValid());
	Assert::same('', $input->getValue());
	Assert::same('Family Guy', $input->getSelectedItem());
	Assert::true($input->isFilled());
});


test('missing input results in null', function () use ($series) {
	$form = new Form;
	$input = $form['missing'] = new ChoiceControl(null, $series);

	Assert::true($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('disabled input ignores submission', function () use ($series) {
	$_POST = ['disabled' => 'red-dwarf'];

	$form = new Form;
	$input = $form['disabled'] = new ChoiceControl(null, $series);
	$input->setDisabled();

	Assert::true($form->isValid());
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('malformed array input handling', function () use ($series) {
	$_POST = ['malformed' => ['']];

	$form = new Form;
	$input = $form['malformed'] = new ChoiceControl(null, $series);

	Assert::true($form->isValid());
	Assert::null($input->getValue());
	Assert::null($input->getSelectedItem());
	Assert::false($input->isFilled());
});


test('using keys as items without labels', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form['select'] = new ChoiceControl;
	$input->setItems(array_keys($series), useKeys: false);
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


testException('exception on invalid value', function () use ($series) {
	$form = new Form;
	$input = $form['select'] = new ChoiceControl(null, $series);
	$input->setValue('unknown');
}, Nette\InvalidArgumentException::class, "Value 'unknown' is out of allowed set ['red-dwarf', 'the-simpsons', 0, ''] in field 'select'.");


test('invalid value ignored with checkDefaultValue', function () use ($series) {
	$form = new Form;
	$input = $form['select'] = new ChoiceControl(null, $series);
	$input->checkDefaultValue(false);
	$input->setValue('unknown');
	Assert::null($input->getValue());
});


test('dateTime object as value', function () {
	$form = new Form;
	$input = $form['select'] = new ChoiceControl(null, ['2013-07-05 00:00:00' => 1]);
	$input->setValue(new DateTime('2013-07-05'));

	Assert::same('2013-07-05 00:00:00', $input->getValue());
});


test('dateTime items without keys', function () {
	$form = new Form;
	$input = $form['select'] = new ChoiceControl;
	$input->setItems([new DateTime('2013-07-05')], useKeys: false)
		->setValue(new DateTime('2013-07-05'));

	Assert::same('2013-07-05 00:00:00', $input->getValue());
});


test('disabled items ignored', function () use ($series) {
	$_POST = ['select' => 'red-dwarf'];

	$form = new Form;
	$input = $form['select'] = new ChoiceControl(null, $series);
	$input->setDisabled(['red-dwarf']);

	Assert::null($input->getValue());

	unset($form['select']);
	$input = new ChoiceControl(null, $series);
	$input->setDisabled(['red-dwarf']);
	$form['select'] = $input;

	Assert::null($input->getValue());
});

test('items with null labels', function () {
	$_POST = ['select' => '1'];

	$form = new Form;
	$input = $form['select'] = new ChoiceControl(null);
	$input->setItems([
		1 => null,
		2 => 'Red dwarf',
	]);

	Assert::same(1, $input->getValue());
});
