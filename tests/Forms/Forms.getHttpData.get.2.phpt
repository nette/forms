<?php

/**
 * Test: Nette\Forms HTTP data.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_GET = $_POST = $_FILES = [];
});


test('', function () {
	$_GET = ['item'];
	$_SERVER['REQUEST_URI'] = '/?' . http_build_query($_GET);

	$form = new Form;
	$form->setMethod($form::Get);
	$form->addSubmit('send', 'Send');

	Assert::truthy($form->isSubmitted());
	Assert::same(['item'], $form->getHttpData());
	Assert::same([], $form->getValues(true));
});
