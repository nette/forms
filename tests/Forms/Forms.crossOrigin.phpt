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
	Form::initialize(true);
});


test('missing Sec-Fetch header in POST', function () {
	$form = new Form;
	Assert::false($form->isSubmitted());
});


test('present Sec-Fetch header in POST', function () {
	$request = new Nette\Http\Request(new Nette\Http\UrlScript, method: 'POST', headers: ['Sec-Fetch-Site' => 'same-origin']);
	Form::initialize($request);

	$form = new Form;
	Assert::true($form->isSuccess());
});


test('allowed cross-origin form submission', function () {
	$form = new Form;
	$form->allowCrossOrigin();
	Assert::true($form->isSuccess());
});
