<?php

/**
 * Test: Nette\Forms success and validate callback takes $form and $values parameters.
 */

declare(strict_types=1);

use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$_SERVER['REQUEST_METHOD'] = 'POST';
$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
$_POST['text'] = 'a';
$_POST['btn'] = 'b';

$form = new Form;
$form->addText('text');
$form->addSubmit('btn');

$types = [];

// Test the closure second parameter to be an ArrayHash instance by default
$f1 = function ($form, $values) use (&$types) {
	$types['f1'][] = $form instanceof Form;
	$types['f1'][] = $values instanceof ArrayHash;
};

// Test the closure second parameter to be an array by type-hint
$f2 = function ($form, array $values) use (&$types) {
	$types['f2'][] = $form instanceof Form;
	$types['f2'][] = is_array($values);
};

// Test the closure second parameter to be an ArrayHash instance by default
$b1 = function ($form, $values) use (&$types) {
	$types['b1'][] = $form instanceof SubmitButton;
	$types['b1'][] = $values instanceof ArrayHash;
};

// Test the closure second parameter to be an array by type-hint
$b2 = function ($form, array $values) use (&$types) {
	$types['b2'][] = $form instanceof SubmitButton;
	$types['b2'][] = is_array($values);
};

// Test the second parameter in ArrayHash form to be immutable
$f4 = function ($form, $values) use (&$types) {
	$values->text = 'b';
};
$arrayHashIsImmutable = [];
$f5 = function ($form, $values) use (&$arrayHashIsImmutable) {
	$arrayHashIsImmutable[] = $values->text === 'a';
};

// Test the closure single parameter without type-hint
$f6 = function ($form) use (&$types) {
	$types['f6'][] = $form instanceof Form;
};

// Test the closure single array parameter
$f7 = function (array $values) use (&$types) {
	$types['f7'][] = is_array($values);
};

// Test the closure single Form-typed parameter
$f8 = function (Form $values) use (&$types) {
	$types['f8'][] = $values instanceof Form;
};

// Test the closure single not-Form-typed parameter
$f9 = function (ArrayHash $values) use (&$types) {
	$types['f9'][] = $values instanceof ArrayHash;
};

// Test the closure single parameter without type-hint
$b3 = function ($form) use (&$types) {
	$types['b3'][] = $form instanceof SubmitButton;
};

// Test the closure single array parameter
$b4 = function (array $values) use (&$types) {
	$types['b4'][] = is_array($values);
};

// Test the closure single parameter SubmitButton-typed
$b5 = function (SubmitButton $form) use (&$types) {
	$types['b5'][] = $form instanceof SubmitButton;
};


$form->onSuccess = [$f1, $f2, $f1, $f4, $f5, $f6, $f7, $f8, $f9];
$form->onValidate = [$f1, $f2, $f1, $f4, $f5, $f6, $f7, $f8, $f9];
$form['btn']->onClick = [$b1, $b2, $b1, $f4, $f5, $b3, $b4, $b5];
$form->fireEvents();

Assert::same([
	'f1' => [true, true, true, true, true, true, true, true],
	'f2' => [true, true, true, true],
	'f6' => [true, true],
	'f7' => [true, true],
	'f8' => [true, true],
	'f9' => [true, true],
	'b1' => [true, true, true, true],
	'b2' => [true, true],
	'b3' => [true],
	'b4' => [true],
	'b5' => [true],
], $types);
Assert::same([true, true, true], $arrayHashIsImmutable);
