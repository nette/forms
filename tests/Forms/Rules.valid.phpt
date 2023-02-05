<?php

/**
 * Test: Nette\Forms\Rules.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('', function () {
	$form = new Form;
	$form->addText('foo')
		->setRequired('fill foo');
	$form->addText('bar')
		->addConditionOn($form['foo'], Form::Valid)
		->setRequired('fill bar');

	$form->validate();
	Assert::same(['fill foo'], $form->getErrors());

	$form['foo']->setValue('abc');
	$form->validate();
	Assert::same(['fill bar'], $form->getErrors());

	$form['bar']->setValue('abc');
	$form->validate();
	Assert::same([], $form->getErrors());
});


testException('', function () {
	$form = new Form;
	$form->addText('foo')
		->addRule(Form::Valid);
}, Nette\InvalidArgumentException::class, 'You cannot use Form::Valid in the addRule method.');


test('', function () {
	$form = new Form;
	$form->addText('foo')
		->addFilter(fn($value) => str_replace(' ', '', $value))
		->addRule($form::Pattern, 'only numbers', '\d{5}');

	$form['foo']->setValue('160 00');
	$form->validate();
	Assert::same([], $form->getErrors());

	$form['foo']->setValue('160 00 x');
	$form->validate();
	Assert::same(['only numbers'], $form->getErrors());
});


test('', function () {
	$form = new Form;
	$foo = $form->addText('foo');
	$rules = $foo->getRules();
	$rules->addFilter(
		fn($value) => str_replace(' ', '', $value),
	);
	$rules->addRule($form::Pattern, 'only numbers', '\d{5}');

	$form['foo']->setValue('160 00');
	$form->validate();
	Assert::same([], $form->getErrors());

	$form['foo']->setValue('160 00 x');
	$form->validate();
	Assert::same(['only numbers'], $form->getErrors());
});


testException('', function () {
	$form = new Form;
	$form->addText('foo')
		->addCondition(Form::Valid);
}, Nette\InvalidArgumentException::class, 'You cannot use Form::Valid in the addCondition method.');


testException('', function () {
	$form = new Form;
	@$form->addText('foo')
		->addRule(~Form::Valid); // @ - negative rules are deprecated
}, Nette\InvalidArgumentException::class, 'You cannot use Form::Valid in the addRule method.');
