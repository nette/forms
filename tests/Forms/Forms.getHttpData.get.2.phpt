<?php

/**
 * Test: Nette\Forms HTTP data.
 */

use Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function() {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_GET = $_POST = $_FILES = [];
});


test(function() {
	$_GET = ['item'];
	$_SERVER['REQUEST_URI'] = '/?' . http_build_query($_GET);

	$form = new Form;
	$form->setMethod($form::GET);
	$form->addSubmit('send', 'Send');

	Assert::truthy( $form->isSubmitted() );
	Assert::same( ['item'], $form->getHttpData() );
	Assert::same( [], $form->getValues(TRUE) );
});
