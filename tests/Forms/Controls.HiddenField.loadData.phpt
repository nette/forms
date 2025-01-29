<?php

/**
 * Test: Nette\Forms\Controls\HiddenField.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
	ob_start();
	Form::initialize(true);
});


test('input normalization', function () {
	$_POST = ['text' => "  a\r b \n c "];
	$form = new Form;
	$input = $form->addHidden('text');
	Assert::same("  a\n b \n c ", $input->getValue());
	Assert::true($input->isFilled());
});


test('missing POST data handling', function () {
	$form = new Form;
	$input = $form->addHidden('unknown');
	Assert::same('', $input->getValue());
	Assert::false($input->isFilled());
});


test('malformed array input', function () {
	$_POST = ['malformed' => ['']];
	$form = new Form;
	$input = $form->addHidden('malformed');
	Assert::same('', $input->getValue());
	Assert::false($input->isFilled());
});


test('error propagation to form', function () {
	$form = new Form;
	$input = $form->addHidden('hidden');
	$input->addError('error');
	Assert::same([], $input->getErrors());
	Assert::same(['error'], $form->getErrors());
});


testException('array value exception', function () {
	$form = new Form;
	$input = $form->addHidden('hidden');
	$input->setValue([]);
}, Nette\InvalidArgumentException::class, "Value must be scalar or null, array given in field 'hidden'.");


test('object value retention', function () {
	$form = new Form;
	$input = $form->addHidden('hidden')
		->setValue($data = new Nette\Utils\DateTime('2013-07-05'));

	Assert::same($data, $input->getValue());
});


test('filter application on validation', function () {
	$date = new Nette\Utils\DateTime('2013-07-05');
	$_POST = ['text' => (string) $date];
	$form = new Form;
	$input = $form->addHidden('text');
	$input->addFilter(fn($value) => $value ? new Nette\Utils\DateTime($value) : $value);

	Assert::same((string) $date, $input->getValue());
	$input->validate();
	Assert::equal($date, $input->getValue());
});


test('integer validation and conversion', function () {
	$_POST = ['text' => '10'];
	$form = new Form;
	$input = $form->addHidden('text');
	$input->addRule($form::Integer);

	Assert::same('10', $input->getValue());
	$input->validate();
	Assert::equal(10, $input->getValue());
});


test('persistent value handling', function () {
	$form = new Form;
	$input = $form['hidden'] = new Nette\Forms\Controls\HiddenField('persistent');
	$input->setValue('other');

	Assert::same('persistent', $input->getValue());
});


test('nullable with empty string', function () {
	$form = new Form;
	$input = $form->addHidden('hidden');
	$input->setValue('');
	$input->setNullable();
	Assert::null($input->getValue());
});


test('nullable with null', function () {
	$form = new Form;
	$input = $form->addHidden('hidden');
	$input->setValue(null);
	$input->setNullable();
	Assert::null($input->getValue());
});
