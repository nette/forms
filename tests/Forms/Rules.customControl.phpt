<?php declare(strict_types=1);

/**
 * Test: Nette\Forms rules Filled, Blank and Valid on a custom Control implementation.
 */

use Nette\Forms\{Control, Form, Rules};
use Nette\Utils\Html;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class CustomControl extends Nette\ComponentModel\Component implements Control
{
	private mixed $value = null;
	private array $errors = [];
	private ?Rules $rules = null;


	public function setValue(mixed $value): static
	{
		$this->value = $value;
		return $this;
	}


	public function getValue(): mixed
	{
		return $this->value;
	}


	public function loadHttpData(): void
	{
	}


	public function isDisabled(): bool
	{
		return false;
	}


	public function getHtmlName(): string
	{
		return (string) $this->getName();
	}


	public function getControl(): Html|string
	{
		return '';
	}


	public function getLabel(string|Stringable|null $caption = null): Html|string|null
	{
		return null;
	}


	public function setOption(string $key, mixed $value): static
	{
		return $this;
	}


	public function getOption(string $key): mixed
	{
		return null;
	}


	public function isFilled(): bool
	{
		return $this->value !== null && $this->value !== '';
	}


	public function getRules(): Rules
	{
		return $this->rules ??= new Rules($this);
	}


	public function getForm(bool $throw = true): ?Form
	{
		return $this->lookup(Form::class, $throw);
	}


	public function validate(): void
	{
		$this->getRules()->validate();
	}


	public function addError(string|Stringable $message, bool $translate = true): void
	{
		$this->errors[] = $message;
	}


	public function getErrors(): array
	{
		return $this->errors;
	}


	public function isOmitted(): bool
	{
		return false;
	}
}


test('required', function () {
	$form = new Form;
	$form['custom'] = $control = new CustomControl;
	$control->getRules()->setRequired('required');
	$control->validate();
	Assert::same(['required'], $control->getErrors());
});


test('blank and valid conditions', function () {
	$form = new Form;
	$form['custom'] = $control = new CustomControl;
	$control->setValue('x');
	$control->getRules()
		->addCondition(Form::Blank)
			->addRule(fn() => false, 'blank');
	$other = $form->addText('other')->setValue('y');
	$other->addConditionOn($control, Form::Valid)
		->addRule(fn() => false, 'valid');

	$control->validate();
	$other->validate();
	Assert::same([], $control->getErrors());
	Assert::same(['valid'], $other->getErrors());
});
