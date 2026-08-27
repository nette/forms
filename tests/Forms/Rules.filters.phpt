<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Rules::addFilter()
 */

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('filter transforms the value during validation', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addFilter(fn($value) => strrev($value));
	$input->setValue('abc');
	$input->validate();
	Assert::same('cba', $input->getValue());
});


test('filter rule validator stays callable (BC)', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addFilter(fn($value) => trim($value));
	[$rule] = iterator_to_array($input->getRules());
	Assert::true(is_callable($rule->validator));

	$input->setValue(' abc ');
	($rule->validator)($input);
	Assert::same('abc', $input->getValue());
});


test('filter prevents the maxlength attribute', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addFilter(fn($value) => $value);
	$input->addRule(Form::MaxLength, null, 10);
	Assert::null($input->getControl()->maxlength);
});


test('filter inside a condition branch prevents the maxlength attribute', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition(Form::Filled)
		->addFilter(fn($value) => $value);
	$input->addRule(Form::MaxLength, null, 10);
	Assert::null($input->getControl()->maxlength);
});


test('server-only callable rule does not prevent the maxlength attribute', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addRule(fn() => true, 'server only');
	$input->addRule(Form::MaxLength, null, 10);
	Assert::same(10, $input->getControl()->maxlength);
});
