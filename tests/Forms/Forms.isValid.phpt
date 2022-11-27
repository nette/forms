<?php

/**
 * Test: Nette\Forms isValid.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
	$_GET = $_POST = $_FILES = [];
});


test('', function () {
	$form = new Form;
	$form->addText('item');

	Assert::true($form->isSubmitted());
	Assert::true($form->isValid());
	Assert::true($form->isSuccess());
	Assert::same([], $form->getErrors());

	$form['item']->addError('1');

	Assert::true($form->isSubmitted());
	Assert::false($form->isValid());
	Assert::false($form->isSuccess());
	Assert::same(['1'], $form->getErrors());

	$form['item']->addError('2');

	Assert::true($form->isSubmitted());
	Assert::false($form->isValid());
	Assert::same(['1', '2'], $form->getErrors());

	$form->validate();

	Assert::true($form->isSubmitted());
	Assert::true($form->isValid());
	Assert::same([], $form->getErrors());
});


test('', function () {
	$form = new Form;
	$form->addText('item');

	$form->addError('1');

	Assert::true($form->isSubmitted());
	Assert::false($form->isValid());
	Assert::same(['1'], $form->getErrors());
});


test('', function () {
	$form = new Form;
	$form->addText('item');

	$form['item']->addError('1');

	Assert::true($form->isSubmitted());
	Assert::false($form->isValid());
	Assert::same(['1'], $form->getErrors());
});


test('', function () {
	$form = new Form;
	$form->addText('item');

	$form->addError('1');
	$form['item']->addError('2');

	Assert::true($form->isSubmitted());
	Assert::false($form->isValid());
	Assert::same(['1', '2'], $form->getErrors());
});


test('', function () {
	$form = new Form;
	$form->addText('item');

	$form->addError('1');
	$form['item']->addError('2');
	$form->onSuccess[] = function () {};
	$form->fireEvents();

	Assert::true($form->isSubmitted());
	Assert::false($form->isValid());
	Assert::same(['1', '2'], $form->getErrors());
});
