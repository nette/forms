<?php

/**
 * Test: Nette\Forms\Controls\ImageButton.
 */

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
});


test(function () {
	$_POST = [
		'image' => [1, 2],
		'container' => [
			'image' => [3, 4],
		],
	];

	$form = new Form;
	$input = $form->addImage('image');
	Assert::true($input->isFilled());
	Assert::same([1, 2], $input->getValue());

	$input = $form->addContainer('container')->addImage('image');
	Assert::same([3, 4], $form['container']['image']->getValue());
});


test(function () { // missing data
	$form = new Form;
	$input = $form->addImage('missing');
	Assert::false($input->isFilled());
	Assert::null($input->getValue());
});


test(function () { // malformed data
	$_POST = [
		'malformed1' => [1],
		'malformed2' => [[NULL]],
	];

	$form = new Form;
	$input = $form->addImage('malformed1');
	Assert::true($input->isFilled());
	Assert::same([1, 0], $input->getValue());

	$input = $form->addImage('malformed2');
	Assert::false($input->isFilled());
	Assert::null($input->getValue());
});
