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
	private $messages = [
		'Login failed' => 'Přihlášení selhalo',
		'Weak password' => 'Slabé heslo',
		'Username' => 'Uživatelské jméno',
		'or email' => 'nebo e-mail',
		'Please enter your username' => 'Vložte prosím své uživatelské jméno',
		'Minimal length is %d chars' => 'Minimální délka je %d znaků',
		'Password' => 'Heslo',
		'Send' => 'Odeslat'
	];

	function translate($message, int $count = NULL): string
	{
		return $this->messages[$message] ?? $message;
	}
}


$form = new Form;
$form->setTranslator(new Translator);

$form->setAction('test');
$form->addError('Login failed');
$form->addText('username', 'Username')->setOption('description', 'or email')->setRequired('Please enter your username');
$form->addPassword('password', 'Password')->setRequired(TRUE)->addRule(Form::MIN_LENGTH, 'Minimal length is %d chars', 8)->addError('Weak password');
$form->addSubmit('submit', 'Send');


Assert::matchFile(__DIR__ . '/Forms.renderer.translate.expect', $form->__toString(TRUE));
