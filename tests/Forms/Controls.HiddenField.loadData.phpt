<?php

/**
 * Test: Nette\Forms\Controls\HiddenField.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';
	Form::initialize(true);
});


test('', function () {
	$_POST = ['text' => "  a\r b \n c "];
	$form = new Form;
	$input = $form->addHidden('text');
	Assert::same("  a\n b \n c ", $input->getValue());
	Assert::true($input->isFilled());
});


test('', function () {
	$form = new Form;
	$input = $form->addHidden('unknown');
	Assert::same('', $input->getValue());
	Assert::false($input->isFilled());
});


test('invalid data', function () {
	$_POST = ['malformed' => ['']];
	$form = new Form;
	$input = $form->addHidden('malformed');
	Assert::same('', $input->getValue());
	Assert::false($input->isFilled());
});


test('errors are moved to form', function () {
	$form = new Form;
	$input = $form->addHidden('hidden');
	$input->addError('error');
	Assert::same([], $input->getErrors());
	Assert::same(['error'], $form->getErrors());
});


test('setValue() and invalid argument', function () {
	$form = new Form;
	$input = $form->addHidden('hidden');
	$input->setValue(null);

	Assert::exception(function () use ($input) {
		$input->setValue([]);
	}, Nette\InvalidArgumentException::class, "Value must be scalar or null, array given in field 'hidden'.");
});


test('object', function () {
	$form = new Form;
	$input = $form->addHidden('hidden')
		->setValue($data = new Nette\Utils\DateTime('2013-07-05'));

	Assert::same($data, $input->getValue());
});


test('object from string by filter', function () {
	$date = new Nette\Utils\DateTime('2013-07-05');
	$_POST = ['text' => (string) $date];
	$form = new Form;
	$input = $form->addHidden('text');
	$input->addFilter(function ($value) {
		return $value ? new \Nette\Utils\DateTime($value) : $value;
	});

	Assert::same((string) $date, $input->getValue());
	$input->validate();
	Assert::equal($date, $input->getValue());
});


test('int from string', function () {
	$_POST = ['text' => '10'];
	$form = new Form;
	$input = $form->addHidden('text');
	$input->addRule($form::INTEGER);

	Assert::same('10', $input->getValue());
	$input->validate();
	Assert::equal(10, $input->getValue());
});


test('persistent', function () {
	$form = new Form;
	$input = $form['hidden'] = new Nette\Forms\Controls\HiddenField('persistent');
	$input->setValue('other');

	Assert::same('persistent', $input->getValue());
});


test('nullable', function () {
	$form = new Form;
	$input = $form->addHidden('hidden');
	$input->setValue('');
	$input->setNullable();
	Assert::null($input->getValue());
});


test('nullable', function () {
	$form = new Form;
	$input = $form->addHidden('hidden');
	$input->setValue(null);
	$input->setNullable();
	Assert::null($input->getValue());
});
