<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () { // valid
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = ['btn' => ''];

	$called = [];
	$form = new Form;
	$form->addText('name');
	$button = $form->addSubmit('btn');

	$button->onClick[] = function (SubmitButton $button) use (&$called) {
		$called[] = 'click';
	};
	$button->onInvalidClick[] = function (SubmitButton $button) use (&$called) {
		$called[] = 'invalidClick';
	};
	$form->onSuccess[] = function (Form $form) use (&$called) {
		$called[] = 'success';
	};
	$form->onError[] = function (Form $form) use (&$called) {
		$called[] = 'error';
	};
	$form->onSubmit[] = function (Form $form) use (&$called) {
		$called[] = 'submit';
	};
	$form->fireEvents();
	Assert::same(['click', 'success', 'submit'], $called);
});


test(function () { // valid -> invalid
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = ['btn' => ''];

	$called = [];
	$form = new Form;
	$form->addText('name');
	$button = $form->addSubmit('btn');

	$button->onClick[] = function (SubmitButton $button) use (&$called) {
		$called[] = 'click1';
	};
	$button->onClick[] = function (SubmitButton $button) use (&$called) {
		$called[] = 'click2';
		$button->getForm()->addError('error');
	};
	$button->onClick[] = function (SubmitButton $button) use (&$called) {
		$called[] = 'click3';
	};
	$button->onInvalidClick[] = function (SubmitButton $button) use (&$called) {
		$called[] = 'invalidClick';
	};
	$form->onSuccess[] = function (Form $form) use (&$called) {
		$called[] = 'success';
	};
	$form->onError[] = function (Form $form) use (&$called) {
		$called[] = 'error';
	};
	$form->onSubmit[] = function (Form $form) use (&$called) {
		$called[] = 'submit';
	};
	$form->fireEvents();
	Assert::same(['click1', 'click2', 'error', 'submit'], $called);
});


test(function () { // invalid
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = ['btn' => ''];

	$called = [];
	$form = new Form;
	$form->addText('name')
		->setRequired();
	$button = $form->addSubmit('btn');

	$button->onClick[] = function (SubmitButton $button) use (&$called) {
		$called[] = 'click';
	};
	$button->onInvalidClick[] = function (SubmitButton $button) use (&$called) {
		$called[] = 'invalidClick';
	};
	$form->onSuccess[] = function (Form $form) use (&$called) {
		$called[] = 'success';
	};
	$form->onError[] = function (Form $form) use (&$called) {
		$called[] = 'error';
	};
	$form->onSubmit[] = function (Form $form) use (&$called) {
		$called[] = 'submit';
	};
	$form->fireEvents();
	Assert::same(['invalidClick', 'error', 'submit'], $called);
});


Assert::exception(function () {
	$form = new Form;
	$form->addSubmit('btn')->onClick = TRUE;
	$form->fireEvents();
}, Nette\UnexpectedValueException::class, "Property \$onClick in button 'btn' must be iterable, boolean given.");
