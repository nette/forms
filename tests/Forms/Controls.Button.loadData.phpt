<?php

/**
 * Test: Nette\Forms\Controls\Button & SubmitButton
 */

use Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function() {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
});


test(function() {
	$_POST = [
		'button' => 'x',
	];

	$form = new Form;
	$input = $form->addSubmit('button');
	Assert::true( $input->isFilled() );
	Assert::same( 'x', $input->getValue() );
});


test(function() { // empty value
	$_POST = [
		'button1' => '',
		'button2' => '0',
	];

	$form = new Form;
	$input = $form->addSubmit('button1');
	Assert::true( $input->isFilled() );
	Assert::same( '', $input->getValue() );

	$form = new Form;
	$input = $form->addSubmit('button2');
	Assert::true( $input->isFilled() );
	Assert::same( '0', $input->getValue() );
});


test(function() { // missing data
	$form = new Form;
	$input = $form->addSubmit('button');
	Assert::false( $input->isFilled() );
	Assert::null( $input->getValue() );
});


test(function() { // malformed data
	$_POST = [
		'malformed' => [],
	];

	$form = new Form;
	$input = $form->addSubmit('malformed');
	Assert::false( $input->isFilled() );
	Assert::null( $input->getValue() );
});
