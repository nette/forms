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
	$_GET = $_POST = $_FILES = $_COOKIE = [];
	ob_start();
	Form::initialize(true);
});


test('crossOrigin', function () {
	$form = new Form;
	Assert::false($form->isSuccess());
});


test('sameSite', function () {
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';

	$form = new Form;
	Assert::true($form->isSuccess());
});


test('allowed crossOrigin', function () {
	$form = new Form;
	$form->allowCrossOrigin();
	Assert::true($form->isSuccess());
});
