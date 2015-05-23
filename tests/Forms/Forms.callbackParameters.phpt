<?php

/**
 * Test: Nette\Forms success and validate callback takes $form and $values parameters.
 */

use Nette\Utils\ArrayHash;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['text'] = 'a';

$form = new Form();
$form->addText('text');
$form->addSubmit('submit');

$types = [];

class TestFormCallbackParameters
{
	public static $results = [];

	public static function doSomething($form, $values)
	{
		static::$results[] = $form instanceof Form;
		static::$results[] = $values instanceof ArrayHash;
	}

	public static function doSomethingWithArray($form, array $values)
	{
		static::$results[] = $form instanceof Form;
		static::$results[] = is_array($values);
	}
}

// Test the method second parameter to be an ArrayHash instance by default
$m1 = ['TestFormCallbackParameters', 'doSomething'];

// Test the method second parameter to be an array by type-hint
$m2 = ['TestFormCallbackParameters', 'doSomethingWithArray'];

// Test the method second parameter to be an ArrayHash instance by default again
$m3 = ['TestFormCallbackParameters', 'doSomething'];

// Test the closure second parameter to be an ArrayHash instance by default
$f1 = function ($form, $values) use (& $types) {
	$types[] = $form instanceof Form;
	$types[] = $values instanceof ArrayHash;
};

// Test the closure second parameter to be an array by type-hint
$f2 = function ($form, array $values) use (& $types) {
	$types[] = $form instanceof Form;
	$types [] = is_array($values);
};

// Test the closure second parameter to be an ArrayHash instance by default again
$f3 = function ($form, $values) use (& $types) {
	$types[] = $form instanceof Form;
	$types[] = $values instanceof ArrayHash;
};

// Test the second parameter in ArrayHash form to be immutable
$f4 = function ($form, $values) use (& $types) {
	$values->text = 'b';
};
$arrayHashIsImmutable = [];
$f5 = function ($form, $values) use (& $arrayHashIsImmutable) {
	$arrayHashIsImmutable[] = $values->text === 'a';
};

foreach ([$m1, $m2, $m3, $f1, $f2, $f3, $f4, $f5] as $f) {
	$form->onSuccess[] = $f;
}
foreach ([$m1, $m2, $m3, $f1, $f2, $f3, $f4, $f5] as $f) {
	$form->onValidate[] = $f;
}
$form->fireEvents();

Assert::same(TestFormCallbackParameters::$results, [TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE]);
Assert::same($types, [TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE]);
Assert::same($arrayHashIsImmutable, [TRUE, TRUE]);
