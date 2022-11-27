<?php

/**
 * Test: Nette\Forms onSuccess.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('valid', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';

	$called = [];
	$form = new Form;
	$form->addText('name');
	$form->addSubmit('submit');

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
	Assert::same(['success', 'submit'], $called);
});


test('valid -> invalid', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';

	$called = [];
	$form = new Form;
	$form->addText('name');
	$form->addSubmit('submit');

	$form->onSuccess[] = function (Form $form) use (&$called) {
		$called[] = 'success1';
	};
	$form->onSuccess[] = function (Form $form) use (&$called) {
		$called[] = 'success2';
		$form['name']->addError('error');
	};
	$form->onSuccess[] = function (Form $form) use (&$called) {
		$called[] = 'success3';
	};
	$form->onError[] = function (Form $form) use (&$called) {
		$called[] = 'error';
	};
	$form->onSubmit[] = function (Form $form) use (&$called) {
		$called[] = 'submit';
	};
	$form->fireEvents();
	Assert::same(['success1', 'success2', 'error', 'submit'], $called);
});


test('invalid', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';

	$called = [];
	$form = new Form;
	$form->addText('name')
		->setRequired();
	$form->addSubmit('submit');

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
	Assert::same(['error', 'submit'], $called);
});
