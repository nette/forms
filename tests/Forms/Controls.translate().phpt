<?php

/**
 * Test: Nette\Forms translating controls with translatable strings wrapped in objects
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Translator implements Nette\Localization\ITranslator
{
	public function translate($message, ...$parameters): string
	{
		return is_object($message) ? get_class($message) : $message;
	}
}

class StringWrapper
{
	public $message;


	public function __construct($message)
	{
		$this->message = $message;
	}


	public function __toString()
	{
		return (string) $this->message;
	}
}



test('', function () {
	$form = new Form;
	$form->setTranslator(new Translator);

	$name = $form->addText('name', 'Your name');
	Assert::match('<label for="frm-name">Your name</label>', (string) $name->getLabel());

	$name2 = $form->addText('name2', new StringWrapper('Your name'));
	Assert::match('<label for="frm-name2">StringWrapper</label>', (string) $name2->getLabel());
});



test('', function () {
	$form = new Form;
	$form->setTranslator(new Translator);

	$name = $form->addRadioList('name', 'Your name');
	Assert::match('<label>Your name</label>', (string) $name->getLabel());

	$name2 = $form->addRadioList('name2', new StringWrapper('Your name'));
	Assert::match('<label>StringWrapper</label>', (string) $name2->getLabel());
});



test('', function () {
	$form = new Form;
	$form->setTranslator(new Translator);

	$name = $form->addText('name', 'Your name');
	$name->addError('Error message');
	$name->addError(new StringWrapper('Your name'));
	Assert::same([
		'Error message',
		'StringWrapper',
	], $name->getErrors());
});



test('', function () {
	$form = new Form;
	$form->setTranslator(new Translator);

	$email = $form->addText('email')
		->setRequired('error')
		->addRule($form::Email, 'error');

	Assert::match('<input type="email" name="email" id="frm-email" required data-nette-rules=\'[{"op":":filled","msg":"error"},{"op":":email","msg":"error"}]\'>', (string) $email->getControl());
	$email->validate();
	Assert::same(['error'], $email->getErrors());

	$email2 = $form->addText('email2')
		->setRequired(new StringWrapper('Your name'))
		->addRule($form::Email, new StringWrapper('Your name'));

	Assert::match('<input type="email" name="email2" id="frm-email2" required data-nette-rules=\'[{"op":":filled","msg":"StringWrapper"},{"op":":email","msg":"StringWrapper"}]\'>', (string) $email2->getControl());
	$email2->validate();
	Assert::same(['StringWrapper'], $email2->getErrors());
});
