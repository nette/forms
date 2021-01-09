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
$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';
$_POST['text'] = 'a';
$_POST['btn'] = 'b';

$form = new Form;
$form->addText('text');
$form->addSubmit('btn');

$types = [];

// Test the closure second parameter to be an ArrayHash instance by default
$f1 = function ($form, $values) use (&$types) {
	$types[] = $form instanceof Form;
	$types[] = $values instanceof ArrayHash;
};

// Test the closure second parameter to be an array by type-hint
$f2 = function ($form, array $values) use (&$types) {
	$types[] = $form instanceof Form;
	$types[] = is_array($values);
};

// Test the closure second parameter to be an ArrayHash instance by default
$b1 = function ($form, $values) use (&$types) {
	$types[] = $form instanceof SubmitButton;
	$types[] = $values instanceof ArrayHash;
};

// Test the closure second parameter to be an array by type-hint
$b2 = function ($form, array $values) use (&$types) {
	$types[] = $form instanceof SubmitButton;
	$types[] = is_array($values);
};

// Test the second parameter in ArrayHash form to be immutable
$f4 = function ($form, $values) use (&$types) {
	$values->text = 'b';
};
$arrayHashIsImmutable = [];
$f5 = function ($form, $values) use (&$arrayHashIsImmutable) {
	$arrayHashIsImmutable[] = $values->text === 'a';
};

$form->onSuccess = [$f1, $f2, $f1, $f4, $f5];
$form->onValidate = [$f1, $f2, $f1, $f4, $f5];
$form['btn']->onClick = [$b1, $b2, $b1, $f4, $f5];
$form->fireEvents();

Assert::same([true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, true], $types);
Assert::same([true, true, true], $arrayHashIsImmutable);
