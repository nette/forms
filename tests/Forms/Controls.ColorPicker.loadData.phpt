<?php

/**
 * Test: Nette\Forms\Controls\ColorPicker.
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


test('loadData empty string', function () {
	$_POST = ['color' => ''];

	$form = new Form;
	$input = $form->addColor('color');

	Assert::same('#000000', $input->getValue());
	Assert::true($input->isFilled());
});


test('loadData invalid string', function () {
	$_POST = ['color' => '#abc'];

	$form = new Form;
	$input = $form->addColor('color');

	Assert::same('#000000', $input->getValue());
	Assert::true($input->isFilled());
});


test('loadData valid string', function () {
	$_POST = ['color' => '#1020aa'];

	$form = new Form;
	$input = $form->addColor('color');

	Assert::same('#1020aa', $input->getValue());
	Assert::true($input->isFilled());
});
