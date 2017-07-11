<?php

/**
 * Test: Nette\Forms default rendering with translator.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Localization\ITranslator;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class Translator implements ITranslator
{
	function translate($message, int $count = null): string
	{
		return strtoupper($message);
	}
}


$form = new Form;
$form->setTranslator(new Translator);

$form->setAction('test');
$form->addError('Login failed');
$form->addText('username', 'Username')
	->setOption('description', 'or email')
	->setRequired('Please enter your username');
$form->addPassword('password', 'Password')
	->setRequired(true)
	->addRule(Form::MIN_LENGTH, 'Minimal length is %d chars', 8)
	->addError('Weak password');
$form->addSubmit('submit', 'Send');


Assert::matchFile(__DIR__ . '/Forms.renderer.translate.expect', $form->__toString(true));
