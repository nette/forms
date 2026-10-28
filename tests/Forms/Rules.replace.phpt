<?php declare(strict_types=1);

/**
 * Test: Nette\Forms an identical rule replaces the existing one.
 */

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('custom message replaces the built-in rule of addEmail()', function () {
	$form = new Form;
	$input = $form->addEmail('mail')
		->addRule(Form::Email, 'Enter a valid e-mail')
		->setDefaultValue('xx');

	$form->validate();
	Assert::same(['Enter a valid e-mail'], $form->getErrors());
	Assert::count(1, iterator_to_array($input->getRules()));
});


test('custom message replaces the built-in rule of addInteger()', function () {
	$form = new Form;
	$form->addInteger('num')
		->addRule(Form::Integer, 'Whole number, please')
		->setDefaultValue('1.5');

	$form->validate();
	Assert::same(['Whole number, please'], $form->getErrors());
});


test('rules with different arguments are kept', function () {
	$form = new Form;
	$input = $form->addText('code')
		->addRule(Form::Pattern, 'Letters only', '[a-z]+')
		->addRule(Form::Pattern, 'Three characters', '...')
		->setDefaultValue('ab');

	$form->validate();
	Assert::same(['Three characters'], $form->getErrors());
	Assert::count(2, iterator_to_array($input->getRules()));
});
