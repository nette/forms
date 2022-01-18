<?php

/**
 * Test: Nette\Forms\Controls\BaseControl & enum
 * @phpVersion 8.1
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Validator;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


enum TestEnum: string
{
	case CASE_1 = 'case 1';
	case CASE_2 = 'case 2';
}


setUp(function () {
	ob_start();
	Form::initialize(true);
});


test('validators for enums', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->setValue(TestEnum::CASE_1->value);

	Assert::true(Validator::validateEqual($input, TestEnum::CASE_1));
	Assert::true(Validator::validateEqual($input, 'case 1'));
	Assert::false(Validator::validateEqual($input, TestEnum::CASE_2));
	Assert::false(Validator::validateEqual($input, 1));
});
