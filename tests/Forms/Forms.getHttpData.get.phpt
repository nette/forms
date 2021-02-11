<?php

/**
 * Test: Nette\Forms HTTP data.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_GET = $_POST = $_FILES = [];
	Form::initialize(true);
});


test('', function () {
	$form = new Form;
	$form->setMethod($form::GET);
	$form->addSubmit('send', 'Send');

	Assert::false($form->isSubmitted());
	Assert::false($form->isSuccess());
	Assert::same([], $form->getHttpData());
	Assert::same([], $form->getValues(true));
});


test('', function () {
	$form = new Form;
	$form->addSubmit('send', 'Send');

	Assert::false($form->isSubmitted());
	Assert::same([], $form->getHttpData());
	Assert::same([], $form->getValues(true));
});


test('', function () {
	$name = 'name';
	$_GET = [Form::TRACKER_ID => $name];
	$_SERVER['REQUEST_URI'] = '/?' . http_build_query($_GET);

	$form = new Form($name);
	$form->setMethod($form::GET);
	$form->addSubmit('send', 'Send');

	Assert::truthy($form->isSubmitted());
	Assert::same([Form::TRACKER_ID => $name], $form->getHttpData());
	Assert::same([], $form->getValues(true));
	Assert::same($name, $form[Form::TRACKER_ID]->getValue());
});
