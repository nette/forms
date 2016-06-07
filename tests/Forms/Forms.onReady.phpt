<?php

/**
 * Test: Nette\Forms onReady.
 */

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';

$called = [];
$form = new Form;
$form->addText('name');
$form->addSubmit('submit');
$form->onReady[] = function (Form $form) use (& $called) {
	$called[] = 'ready';
};
$form->onSuccess[] = function () use (& $called) {
	$called[] = 'success';
};
$form->fireEvents();
Assert::same(['ready', 'success'], $called);


$_SERVER['REQUEST_METHOD'] = 'GET';

$called = [];
$form = new Form;
$form->addText('name');
$form->addSubmit('submit');
$form->onReady[] = function (Form $form) use (& $called) {
	$called[] = 'ready';
};
$form->onSuccess[] = function () use (& $called) {
	$called[] = 'success';
};
$form->fireEvents();
Assert::same(['ready'], $called);
