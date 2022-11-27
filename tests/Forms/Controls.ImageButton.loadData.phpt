<?php

/**
 * Test: Nette\Forms\Controls\ImageButton.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
	Form::initialize(true);
});


test('', function () {
	$_POST = [
		'image' => ['1', '2'],
		'container' => [
			'image' => ['3', '4'],
		],
	];

	$form = new Form;
	$input = $form->addImageButton('image');
	Assert::true($input->isFilled());
	Assert::same([1, 2], $input->getValue());

	$input = $form->addContainer('container')->addImageButton('image');
	Assert::same([3, 4], $form['container']['image']->getValue());
});


test('missing data', function () {
	$form = new Form;
	$input = $form->addImageButton('missing');
	Assert::false($input->isFilled());
	Assert::null($input->getValue());
});


test('malformed data', function () {
	$_POST = [
		'malformed1' => ['1'],
		'malformed2' => [['']],
	];

	$form = new Form;
	$input = $form->addImageButton('malformed1');
	Assert::true($input->isFilled());
	Assert::same([1, 0], $input->getValue());

	$input = $form->addImageButton('malformed2');
	Assert::false($input->isFilled());
	Assert::null($input->getValue());
});
