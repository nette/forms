<?php declare(strict_types=1);

/**
 * PHPStan type tests for Forms.
 * Run: vendor/bin/phpstan analyse tests/types
 */

use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use function PHPStan\Testing\assertType;


class FormDto
{
	public string $name;
}


function testFormContainerGetValues(Container $container): void
{
	assertType('Nette\Utils\ArrayHash<mixed>', $container->getValues());
	assertType('array|array<mixed>', $container->getValues('array'));
	assertType(FormDto::class, $container->getValues(FormDto::class));
}


function testFormContainerGetUntrustedValues(Container $container): void
{
	assertType('Nette\Utils\ArrayHash<mixed>', $container->getUntrustedValues(null));
	assertType('array|array<mixed>', $container->getUntrustedValues('array'));
	assertType(FormDto::class, $container->getUntrustedValues(FormDto::class));
}


function testFormContainerArrayAccess(Container $container): void
{
	assertType('Nette\Forms\Controls\BaseControl', $container['name']);
}


function testFormEvents(Form $form): void
{
	$form->onSuccess[] = function (Form $form, ArrayHash $values): void {
		assertType(Form::class, $form);
		assertType(ArrayHash::class, $values);
	};

	$form->onError[] = function (Form $form): void {
		assertType(Form::class, $form);
	};
}


// Issue #350: addRule() must accept validators with a second $arg parameter
function testAddRuleValidatorOneParam(Form $form): void
{
	$form->addText('field')
		->addRule(fn(Nette\Forms\Control $input): bool => (bool) $input->getValue());
}


function testAddRuleValidatorTwoParams(Form $form): void
{
	$form->addInteger('num')
		->addRule(
			fn(Nette\Forms\Control $input, mixed $arg): bool => $input->getValue() > $arg,
			'Must be greater than %d',
			0,
		);
}


function testAddRuleStaticCallableWithArg(Form $form): void
{
	$form->addInteger('num')
		->addRule(
			[CustomValidators::class, 'validateDivisibility'],
			'Must be divisible by %d',
			8,
		);
}


// addCondition() with one-parameter handler
function testAddConditionOneParam(Form $form): void
{
	$form->addText('field')
		->addCondition(fn(Nette\Forms\Control $input): bool => (bool) $input->getValue())
			->setRequired();
}


// addCondition() with two-parameter handler
function testAddConditionTwoParams(Form $form): void
{
	$form->addInteger('num')
		->addCondition(
			fn(Nette\Forms\Control $input, mixed $arg): bool => $input->getValue() > $arg,
			0,
		)
			->setRequired();
}


class CustomValidators
{
	public static function validateDivisibility(Nette\Forms\Control $input, mixed $arg): bool
	{
		return $input->getValue() % $arg === 0;
	}
}
