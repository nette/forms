<?php

/**
 * Test: Nette\Forms\Rules::formatMessage()
 */

declare(strict_types=1);

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addText('args1')
	->setRequired()
	->addRule(Form::RANGE, '%d %d', [1, 5])
	->setDefaultValue('x');

$form->addText('args2')
	->setRequired()
	->addRule(Form::RANGE, '%2$d %1$d', [1, 5])
	->setDefaultValue('x');

$form->addText('args3')
	->setRequired()
	->addRule(Form::LENGTH, '%d %d', 1)
	->setDefaultValue('xyz');

$form->addText('special', 'Label:')
	->setRequired()
	->addRule(Form::EMAIL, '%label %value is invalid [field %name] %d', $form['special'])
	->setDefaultValue('xyz');

$form->addText('function', 'Label:')
	->setRequired()
	->addRule(function (TextInput $input, string $arg) {
		return strpos($input->getValue(), $arg) === false;
	}, function (TextInput $input, string $arg) {
		return "String “{$input->getValue()}” contains a letter “{$arg}”, which is not allowed";
	}, 'a')
	->setDefaultValue('banana');

$form->addText('functionWithoutArg', 'Label:')
	->setRequired()
	->addRule(function (TextInput $input) {
		return strpos($input->getValue(), 'e') === false;
	}, function (TextInput $input) {
		return "String “{$input->getValue()}” contains a letter “e”, which is not allowed";
	})
	->setDefaultValue('orange');

$form->validate();

Assert::true($form->hasErrors());

Assert::same([
	'1 5',
	'5 1',
	'1 ',
	'Label xyz is invalid [field special] xyz',
	'String “banana” contains a letter “a”, which is not allowed',
	'String “orange” contains a letter “e”, which is not allowed',
], $form->getErrors());

Assert::same([], $form->getOwnErrors());

Assert::same(['1 5'], $form['args1']->getErrors());

Assert::same(['5 1'], $form['args2']->getErrors());

Assert::same(['1 '], $form['args3']->getErrors());

Assert::same(['Label xyz is invalid [field special] xyz'], $form['special']->getErrors());

Assert::match('%A%data-nette-rules=\'%A%{"op":":email","msg":"Label %value is invalid [field special] %0"%A%', $form->__toString(true));
