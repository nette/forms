<?php

/**
 * Test: Nette\Forms\Controls\Checkbox.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';
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


test('setValue() and invalid argument', function () {
	$form = new Form;
	$input = $form->addCheckbox('checkbox');
	$input->setValue(null);

	Assert::exception(function () use ($input) {
		$input->setValue([]);
	}, Nette\InvalidArgumentException::class, "Value must be scalar or null, array given in field 'checkbox'.");
});
