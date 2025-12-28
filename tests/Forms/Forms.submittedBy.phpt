<?php

/**
 * Test: Nette\Forms HTTP data.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_GET = $_POST = $_FILES = [];
	ob_start();
	Form::initialize(true);
});


test('identifying submitted button', function () {
	$_POST = ['send2' => ''];

	$form = new Form;
	$form->allowCrossOrigin();
	$btn1 = $form->addSubmit('send1');
	$btn2 = $form->addSubmit('send2');
	$btn3 = $form->addSubmit('send3');

	Assert::true($form->isSuccess());
	Assert::same($btn2, $form->isSubmitted());
});


test('image button submission detection', function () {
	$_POST = ['send2' => ['x' => '1', 'y' => '1']];

	$form = new Form;
	$form->allowCrossOrigin();
	$btn1 = $form->addImageButton('send1');
	$btn2 = $form->addImageButton('send2');
	$btn3 = $form->addImageButton('send3');

	Assert::true($form->isSuccess());
	Assert::same($btn2, $form->isSubmitted());
});
