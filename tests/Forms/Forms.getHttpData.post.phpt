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
	$_GET = $_POST = $_FILES = [];
	Form::initialize(true);
});


test('GET method in POST context', function () {
	$form = new Form;
	$form->setMethod($form::Get);
	$form->addSubmit('send', 'Send');

	Assert::false($form->isSubmitted());
	Assert::false($form->isSuccess());
	Assert::same([], $form->getHttpData());
	Assert::same([], $form->getValues('array'));
});


test('tracker ID in POST submission', function () {
	$name = 'name';
	$_POST = [Form::TrackerId => $name];

	$form = new Form($name);
	$form->allowCrossOrigin();
	$form->addSubmit('send', 'Send');

	Assert::truthy($form->isSubmitted());
	Assert::same([Form::TrackerId => $name], $form->getHttpData());
	Assert::same([], $form->getValues('array'));
	Assert::same($name, $form[Form::TrackerId]->getValue());
});


test('submit button not pressed', function () {
	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addSubmit('send', 'Send');
	Assert::false($input->isSubmittedBy());
	Assert::false(Validator::validateSubmitted($input));
});


test('successful POST submission', function () {
	$_POST = ['send' => ''];
	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addSubmit('send', 'Send');
	Assert::true($input->isSubmittedBy());
	Assert::true(Validator::validateSubmitted($input));
});
