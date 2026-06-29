<?php declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addText('name');

$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new FormsExtension);
$latte->addProvider('uiControl', ['myForm' => $form]);


test('no warning when only the form name is given', function () use ($latte) {
	Assert::noError(fn() => $latte->compile("{form myForm}{/form}\n"));
});


test('no warning when comma separates name and arguments', function () use ($latte) {
	Assert::noError(fn() => $latte->compile("{form myForm, class => 'a'}{/form}\n"));
});


test('deprecation warning when comma is missing before arguments', function () use ($latte) {
	Assert::error(
		fn() => $latte->compile("{form myForm class => 'a'}{/form}\n"),
		E_USER_DEPRECATED,
		'Missing comma before tag arguments on line 1 at column 14.',
	);
});


test('deprecation warning when comma is missing in {input}', function () use ($latte) {
	Assert::error(
		fn() => $latte->compile("{input name class => 'a'}\n"),
		E_USER_DEPRECATED,
		'Missing comma before tag arguments on line 1 at column 13.',
	);
});


test('deprecation warning when comma is missing in {label}', function () use ($latte) {
	Assert::error(
		fn() => $latte->compile("{label name class => 'a' /}\n"),
		E_USER_DEPRECATED,
		'Missing comma before tag arguments on line 1 at column 13.',
	);
});


test('deprecation warning also fires after the :part syntax', function () use ($latte) {
	Assert::error(
		fn() => $latte->compile("{input name:x class => 'a'}\n"),
		E_USER_DEPRECATED,
		'Missing comma before tag arguments on line 1 at column 15.',
	);
});
