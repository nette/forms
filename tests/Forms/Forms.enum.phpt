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
	case Case1 = 'case 1';
	case Case2 = 'case 2';
}


setUp(function () {
	ob_start();
	Form::initialize(true);
});


test('validators for enums', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->setValue(TestEnum::Case1->value);

	Assert::true(Validator::validateEqual($input, TestEnum::Case1));
	Assert::true(Validator::validateEqual($input, 'case 1'));
	Assert::false(Validator::validateEqual($input, TestEnum::Case2));
	Assert::false(Validator::validateEqual($input, 1));
});
