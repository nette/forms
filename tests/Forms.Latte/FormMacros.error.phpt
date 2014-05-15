<?php

/**
 * Test: FormMacros errors.
 *
 * @author     David Grudl
 */

use Nette\Forms\Form,
	Nette\Bridges\FormsLatte\FormMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
FormMacros::install($latte->getCompiler());

Assert::exception(function() use ($latte) {
	$latte->compile('<form n:form></form>');
}, 'Latte\CompileException', 'Did you mean <form n:name=...> ?');

Assert::exception(function() use ($latte) {
	$latte->compile('<form n:name></form>');
}, 'Latte\CompileException', 'Missing name in n:name.');

Assert::exception(function() use ($latte) {
	$latte->compile('<form n:inner-name></form>');
}, 'Latte\CompileException', 'Unknown attribute n:inner-name, use n:name attribute.');


Assert::exception(function() use ($latte) {
	$latte->compile('<html>{form /}');
}, 'Latte\CompileException', 'Missing form name in {form}.');

Assert::exception(function() use ($latte) {
	$latte->compile('<html>{formContainer /}');
}, 'Latte\CompileException', 'Missing name in {formContainer}.');


Assert::exception(function() use ($latte) {
	$latte->compile('<input n:input>');
}, 'Latte\CompileException', 'Use n:name instead of n:input.');

Assert::exception(function() use ($latte) {
	$latte->compile('<html>{label /}');
}, 'Latte\CompileException', 'Missing name in {label}.');

Assert::exception(function() use ($latte) {
	$latte->compile('<html>{input /}');
}, 'Latte\CompileException', 'Missing name in {input}.');

Assert::exception(function() use ($latte) {
	$latte->compile('<html>{name /}');
}, 'Latte\CompileException', 'Unknown macro {name}, use n:name attribute.');
