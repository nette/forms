<?php declare(strict_types=1);

/**
 * Test: Nette\Forms default rendering with translator.
 */

use Nette\Forms\Form;
use Nette\Localization\ITranslator;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class Translator implements ITranslator
{
	public array $counter = [];


	public function translate($message, ...$parameters): string
	{
		$key = $message instanceof Nette\Forms\Validation\Message
			? $message->id
			: (string) $message;
		@$this->counter[$key]++; // @ intentionally
		return strtoupper((string) $message);
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
	->setRequired()
	->addRule(Form::MinLength, 'Minimal length is %d chars', 8)
	->addError('Weak password');
$form->addSubmit('submit', 'Send');


Assert::matchFile(__DIR__ . '/expected/Forms.renderer.translate.expect', $form->__toString(true));


// Checking whether translation is not duplicated
Assert::same([
	'Login failed' => 1,
	'Weak password' => 1,
	'Username' => 1,
	'or email' => 1,
	'Please enter your username' => 1,
	'Password' => 1,
	'forms.filled' => 1, // default messages are translated by their stable identifier
	'Minimal length is %d chars' => 1,
	'Send' => 1,
], $translator->counter);
