<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Controls\ColorPicker.
 */

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	ob_start();
	Form::initialize(true);
});


test('default color for empty input', function () {
	$_POST = ['color' => ''];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addColor('color');

	Assert::same('#000000', $input->getValue());
	Assert::true($input->isFilled());
});


test('invalid color format handling', function () {
	$_POST = ['color' => '#abc'];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addColor('color');

	Assert::same('#000000', $input->getValue());
	Assert::true($input->isFilled());
});


test('valid color value handling', function () {
	$_POST = ['color' => '#1020aa'];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addColor('color');

	Assert::same('#1020aa', $input->getValue());
	Assert::true($input->isFilled());
});


test('too long values are rejected, not truncated', function () {
	$_POST = ['color' => '#336699ff'];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addColor('color');

	Assert::same('#000000', $input->getValue());

	Assert::exception(
		fn() => $input->setValue('1234567'),
		Nette\InvalidArgumentException::class,
		'Color must have #rrggbb format.',
	);
});
