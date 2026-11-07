<?php declare(strict_types=1);

/**
 * PHPStan type tests for Forms.
 * Run: vendor/bin/phpstan analyse tests/types
 */

use Nette\Forms\{Container, Form};
use Nette\Utils\ArrayHash;
use function PHPStan\Testing\assertType;


class FormDto
{
	public string $name;
}


function testFormContainerGetValues(Container $container): void
{
	assertType('Nette\Utils\ArrayHash<mixed>', $container->getValues());
	assertType('array<int|string, mixed>', $container->getValues('array'));
	assertType('array<int|string, mixed>', $container->getValues(Container::Array));
	assertType(FormDto::class, $container->getValues(FormDto::class));
	assertType('array<int|string, mixed>|Nette\Utils\ArrayHash<mixed>', $container->getValues(rand() ? 'array' : null));
	assertType('array<int|string, mixed>|FormDto', $container->getValues(rand() ? 'array' : FormDto::class));
}


function testFormContainerGetUntrustedValues(Container $container): void
{
	assertType('Nette\Utils\ArrayHash<mixed>', $container->getUntrustedValues());
	assertType('array<int|string, mixed>', $container->getUntrustedValues('array'));
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


// Issue #351: getHttpData() without arguments returns the whole nested data, not string[]
function testFormGetHttpData(Form $form): void
{
	assertType('array<mixed>', $form->getHttpData());
	assertType('array<Nette\Http\FileUpload|string>|Nette\Http\FileUpload|string|null', $form->getHttpData(Form::DataLine, 'name'));
}


function testFormGetSubmittedData(Form $form, Nette\Forms\Container $container): void
{
	assertType('array<mixed>', $form->getSubmittedData());
	assertType('array<mixed>', $container->getSubmittedData());
}


function testFormContainerControls(Container $container): void
{
	assertType('Iterator<string, Nette\Forms\Control>', $container->controls);
	assertType('iterable<string, Nette\Forms\Control>', $container->getControls());
}


function testFormErrors(Form $form, Nette\Forms\Controls\TextInput $input): void
{
	assertType('list<string|Stringable>', $form->errors);
	assertType('list<string|Stringable>', $form->ownErrors);
	assertType('list<string|Stringable>', $input->errors);
}


function testUploadControlGetValue(Nette\Forms\Controls\UploadControl $upload): void
{
	assertType('list<Nette\Http\FileUpload>|Nette\Http\FileUpload|null', $upload->getValue());
}
