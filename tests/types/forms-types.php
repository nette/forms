<?php

/**
 * PHPStan type tests for Forms.
 * Run: vendor/bin/phpstan analyse tests/types
 */

declare(strict_types=1);

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
	assertType('Nette\ComponentModel\IComponent', $container['name']);
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
