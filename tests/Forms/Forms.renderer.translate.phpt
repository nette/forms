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
	public array $counter = [];


	public function translate($message, ...$parameters): string
	{
		@$this->counter[$message]++; // @ intentionally
		return strtoupper($message);
	}
}


$translator = new Translator;
$form = new Form;
$form->setTranslator($translator);

$form->setAction('test');
$form->addError('Login failed');
$form->addText('username', 'Username')
	->setOption('description', 'or email')
	->setRequired('Please enter your username');
$form->addPassword('password', 'Password')
	->setRequired(true)
	->addRule(Form::MinLength, 'Minimal length is %d chars', 8)
	->addError('Weak password');
$form->addSubmit('submit', 'Send');


Assert::matchFile(__DIR__ . '/Forms.renderer.translate.expect', $form->__toString(true));


// Checking whether translation is not duplicated
Assert::same([
	'Login failed' => 1,
	'Weak password' => 1,
	'Username' => 1,
	'or email' => 1,
	'Please enter your username' => 1,
	'Password' => 1,
	'This field is required.' => 1,
	'Minimal length is %d chars' => 1,
	'Send' => 1,
], $translator->counter);
