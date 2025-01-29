<?php

/**
 * Test: Nette\Forms\Controls\Button & SubmitButton
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
	ob_start();
	Form::initialize(true);
});


test('submit button captures POST value', function () {
	$_POST = [
		'button' => 'x',
	];

	$form = new Form;
	$input = $form->addSubmit('button');
	Assert::true($input->isFilled());
	Assert::same('x', $input->getValue());
});


test('submit button with empty and zero values', function () {
	$_POST = [
		'button1' => '',
		'button2' => '0',
	];

	$form = new Form;
	$input = $form->addSubmit('button1');
	Assert::true($input->isFilled());
	Assert::same('', $input->getValue());

	$form = new Form;
	$input = $form->addSubmit('button2');
	Assert::true($input->isFilled());
	Assert::same('0', $input->getValue());
});


test('unsubmitted button state', function () {
	$form = new Form;
	$input = $form->addSubmit('button');
	Assert::false($input->isFilled());
	Assert::null($input->getValue());
});


test('handling malformed POST data for button', function () {
	$_POST = [
		'malformed' => [],
	];

	$form = new Form;
	$input = $form->addSubmit('malformed');
	Assert::false($input->isFilled());
	Assert::null($input->getValue());
});
