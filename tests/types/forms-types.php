<?php

/**
 * PHPStan type tests for Forms.
 * Run: vendor/bin/phpstan analyse tests/types
 */

declare(strict_types=1);

use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use function PHPStan\Testing\assertType;


class FormDto
{
	public string $name;
}


function testFormContainerGetValues(Container $container): void
{
	$values = $container->getValues();
	assertType(ArrayHash::class, $values);

	$valuesArray = $container->getValues(asArray: true);
	assertType('array<string, mixed>', $valuesArray);

	$valuesDto = $container->getValues(FormDto::class);
	assertType(FormDto::class, $valuesDto);
}


function testFormContainerGetUnsafeValues(Container $container): void
{
	$values = $container->getUnsafeValues(null);
	assertType(ArrayHash::class, $values);

	$valuesArray = $container->getUnsafeValues('array');
	assertType('array<string, mixed>', $valuesArray);

	$valuesDto = $container->getUnsafeValues(FormDto::class);
	assertType(FormDto::class, $valuesDto);
}


function testFormContainerArrayAccess(Container $container): void
{
	$control = $container['name'];
	assertType(BaseControl::class, $control);
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
