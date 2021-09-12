<?php

/**
 * Test: Nette\Forms HTTP data.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Validator;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
	$_GET = $_POST = $_FILES = [];
	ob_start();
	Form::initialize(true);
});


test('', function () {
	$form = new Form;
	$form->addSubmit('send', 'Send');

	Assert::truthy($form->isSubmitted());
	Assert::true($form->isSuccess());
	Assert::same([], $form->getHttpData());
	Assert::same([], $form->getValues('array'));
});


test('', function () {
	unset($_COOKIE[Nette\Http\Helpers::StrictCookieName]);

	$form = new Form;
	$form->addSubmit('send', 'Send');

	Assert::false($form->isSubmitted());
	Assert::false($form->isSuccess());
	Assert::same([], $form->getHttpData());
	Assert::same([], $form->getValues('array'));
});


test('', function () {
	$form = new Form;
	$form->setMethod($form::Get);
	$form->addSubmit('send', 'Send');

	Assert::false($form->isSubmitted());
	Assert::false($form->isSuccess());
	Assert::same([], $form->getHttpData());
	Assert::same([], $form->getValues('array'));
});


test('', function () {
	$name = 'name';
	$_POST = [Form::TrackerId => $name];

	$form = new Form($name);
	$form->addSubmit('send', 'Send');

	Assert::truthy($form->isSubmitted());
	Assert::same([Form::TrackerId => $name], $form->getHttpData());
	Assert::same([], $form->getValues('array'));
	Assert::same($name, $form[Form::TrackerId]->getValue());
});


test('', function () {
	$form = new Form;
	$input = $form->addSubmit('send', 'Send');
	Assert::false($input->isSubmittedBy());
	Assert::false(Validator::validateSubmitted($input));
});


test('', function () {
	$_POST = ['send' => ''];
	$form = new Form;
	$input = $form->addSubmit('send', 'Send');
	Assert::true($input->isSubmittedBy());
	Assert::true(Validator::validateSubmitted($input));
});
