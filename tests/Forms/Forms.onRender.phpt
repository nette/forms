<?php

/**
 * Test: Nette\Forms onRender.
 */

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$called = [];
$form = new Form;
$form->addText('name');
$form->addSubmit('submit');
$form->onRender[] = function (Form $form) use (&$called) {
	$called[] = 1;
};
$form->fireRenderEvents();
$form->fireRenderEvents();
Assert::same([1], $called);


$called = [];
$form = new Form;
$form->addText('name');
$form->addSubmit('submit');
$form->onRender[] = function (Form $form) use (&$called) {
	$called[] = 1;
};
$form->render();
$form->render();
Assert::same([1], $called);


$called = [];
$form = new Form;
$form->addText('name');
$form->addSubmit('submit');
$form->onRender[] = function (Form $form) use (&$called) {
	$called[] = 1;
};
$form->__toString();
$form->__toString();
Assert::same([1], $called);
