<?php

/**
 * Test: Nette\Forms\Controls\TextInput.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class ValueObject
{
	private string $value;


	public function __construct(string $value)
	{
		$this->value = $value;
	}


	public function __toString(): string
	{
		return $this->value;
	}
}


test('e-mail', function (): void {
	$form = new Form;
	$input = $form->addEmail('email');

	$input->setValue(new ValueObject('example@example.com'));
	Assert::type(ValueObject::class, $input->getValue());
	$form->validate();
	Assert::same([], $form->getErrors());
});


test('URL', function (): void {
	$form = new Form;
	$input = $form->addText('url')
		->addRule(Form::URL);

	$input->setValue(new ValueObject('https://example.com'));
	Assert::type(ValueObject::class, $input->getValue());
	$form->validate();
	Assert::same([], $form->getErrors());

	$input->setValue(new ValueObject('example.com'));
	Assert::type(ValueObject::class, $input->getValue());
	$form->validate();
	Assert::same([], $form->getErrors());
});
