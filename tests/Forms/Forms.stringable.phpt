<?php declare(strict_types=1);

/**
 * Test: Nette\Forms messages and translations as Stringable objects.
 */

use Nette\Forms\Form;
use Nette\Localization\Translator;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class Message implements Stringable
{
	public function __construct(
		private string $text,
	) {
	}


	public function __toString(): string
	{
		return $this->text;
	}
}


test('rule message as Stringable', function () {
	$form = new Form;
	$form->addText('mail', 'Mail:')
		->addRule(Form::Email, new Message('%value of %label is not an e-mail'))
		->setDefaultValue('xx');

	$form->validate();
	Assert::same(['xx of Mail is not an e-mail'], $form->getErrors());
	Assert::contains('"msg":"%value of Mail is not an e-mail"', (string) $form['mail']->getControl());
});


test('translator returning Stringable', function () {
	$form = new Form;
	$form->setTranslator(new class implements Translator {
		public function translate(string|Stringable $message, mixed ...$parameters): Stringable
		{
			return new Message("[$message]");
		}
	});

	$form->addText('mail', 'Mail')
		->setEmptyValue('none')
		->addRule(Form::Email, '%value is invalid')
		->setDefaultValue('xx');
	$form->addSelect('select', null, ['group' => ['a' => 'item']])
		->setDefaultValue('a');
	$form->addMultiSelect('multi', null, ['group' => ['a' => 'item']]);

	$form->validate();
	Assert::same(['[xx is invalid]'], $form->getErrors());
	Assert::same('xx', $form['mail']->getValue());
	Assert::contains('data-nette-empty-value="[none]"', (string) $form['mail']->getControl());
	Assert::contains('<optgroup label="[group]">', (string) $form['select']->getControl());
	Assert::contains('<optgroup label="[group]">', (string) $form['multi']->getControl());
});
