<?php

/**
 * Test: Nette\Forms\Controls\Checkbox.
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


test('', function () {
	$_POST = [
		'off' => '',
		'on' => '1',
	];

	$form = new Form;
	$input = $form->addCheckbox('off');

	Assert::false($input->getValue());
	Assert::false($input->isFilled());

	$input = $form->addCheckbox('on');

	Assert::true($input->getValue());
	Assert::true($input->isFilled());
});


test('malformed data', function () {
	$_POST = ['malformed' => ['']];

	$form = new Form;
	$input = $form->addCheckbox('malformed');

	Assert::false($input->getValue());
	Assert::false($input->isFilled());
});


testException('setValue() and invalid argument', function () {
	$form = new Form;
	$input = $form->addCheckbox('checkbox');
	$input->setValue([]);
}, Nette\InvalidArgumentException::class, "Value must be scalar or null, array given in field 'checkbox'.");
