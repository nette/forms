<?php

/**
 * Test: FormMacros errors.
 */

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormMacros;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '>')) {
	Tester\Environment::skip('Test for Latte 2');
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
FormMacros::install($latte->getCompiler());

Assert::exception(function () use ($latte) {
	$latte->compile('<form n:form></form>');
}, Latte\CompileException::class, 'Did you mean <form n:name=...> ?');

Assert::exception(function () use ($latte) {
	$latte->compile('<form n:name></form>');
}, Latte\CompileException::class, 'Missing name in n:name');

Assert::exception(function () use ($latte) {
	$latte->compile('<form n:inner-name></form>');
}, Latte\CompileException::class, 'Unknown attribute n:inner-name, use n:name attribute.');


Assert::exception(function () use ($latte) {
	$latte->compile('<html>{form /}');
}, Latte\CompileException::class, 'Missing form name in {form}');

Assert::exception(function () use ($latte) {
	$latte->compile('<html>{formContainer /}');
}, Latte\CompileException::class, 'Missing name in {formContainer}');


Assert::exception(function () use ($latte) {
	$latte->compile('<html>{label /}');
}, Latte\CompileException::class, 'Missing name in {label}');

Assert::exception(function () use ($latte) {
	$latte->compile('<html>{input /}');
}, Latte\CompileException::class, 'Missing name in {input}');

Assert::exception(function () use ($latte) {
	$latte->compile('<html>{name /}');
}, Latte\CompileException::class, 'Unknown tag {name}, use n:name attribute.');
