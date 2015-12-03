<?php

/**
 * Test: Nette\Forms\Rules.
 */

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () {
	$form = new Form;
	$form->addText('foo')
		->setRequired('fill foo');
	$form->addText('bar')
		->addConditionOn($form['foo'], Form::VALID)
		->setRequired('fill bar');

	$form->validate();
	Assert::same(array('fill foo'), $form->getErrors());

	$form['foo']->setValue('abc');
	$form->validate();
	Assert::same(array('fill bar'), $form->getErrors());

	$form['bar']->setValue('abc');
	$form->validate();
	Assert::same(array(), $form->getErrors());
});


test(function () {
	Assert::exception(function () {
		$form = new Form;
		$form->addText('foo')
			->addRule(Form::VALID);
	}, 'Nette\InvalidArgumentException', 'You cannot use Form::VALID in the addRule method.');
});


test(function () {
	Assert::exception(function () {
		$form = new Form;
		$form->addText('foo')
			->addCondition(Form::VALID);
	}, 'Nette\InvalidArgumentException', 'You cannot use Form::VALID in the addCondition method.');
});


test(function () {
	Assert::exception(function () {
		$form = new Form;
		$form->addText('foo')
			->addRule(~Form::VALID);
	}, 'Nette\InvalidArgumentException', 'You cannot use Form::VALID in the addRule method.');
});
