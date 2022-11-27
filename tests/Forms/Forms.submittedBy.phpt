<?php

/**
 * Test: Nette\Forms HTTP data.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
	$_GET = $_POST = $_FILES = [];
	Form::initialize(true);
});


test('', function () {
	$name = 'name';
	$_POST = [Form::TrackerId => $name, 'send2' => ''];

	$form = new Form($name);
	$btn1 = $form->addSubmit('send1');
	$btn2 = $form->addSubmit('send2');
	$btn3 = $form->addSubmit('send3');

	Assert::true($form->isSuccess());
	Assert::same($btn2, $form->isSubmitted());
});


test('', function () {
	$name = 'name';
	$_POST = [Form::TrackerId => $name, 'send2' => ['x' => '1', 'y' => '1']];

	$form = new Form($name);
	$btn1 = $form->addImageButton('send1');
	$btn2 = $form->addImageButton('send2');
	$btn3 = $form->addImageButton('send3');

	Assert::true($form->isSuccess());
	Assert::same($btn2, $form->isSubmitted());
});
