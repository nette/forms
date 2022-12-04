<?php

/**
 * Test: Nette\Forms\Rules::formatMessage()
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addText('args1')
	->setRequired()
	->addRule(Form::Range, '%d %d', [1, 5])
	->setDefaultValue('x');

$form->addText('args2')
	->setRequired()
	->addRule(Form::Range, '%2$d %1$d', [1, 5])
	->setDefaultValue('x');

$form->addText('args3')
	->setRequired()
	->addRule(Form::Length, '%d %d', 1)
	->setDefaultValue('xyz');

$form->addText('special', 'Label:')
	->setRequired()
	->addRule(Form::Email, '%label %value is invalid [field %name] %d', $form['special'])
	->setDefaultValue('xyz');

$form->validate();

Assert::true($form->hasErrors());

Assert::same(['1 5', '5 1', '1 ', 'Label xyz is invalid [field special] xyz'], $form->getErrors());

Assert::same([], $form->getOwnErrors());

Assert::same(['1 5'], $form['args1']->getErrors());

Assert::same(['5 1'], $form['args2']->getErrors());

Assert::same(['1 '], $form['args3']->getErrors());

Assert::same(['Label xyz is invalid [field special] xyz'], $form['special']->getErrors());

Assert::match('%A%data-nette-rules=\'%A%{"op":":email","msg":"Label %value is invalid [field special] %0"%A%', $form->__toString(true));
