<?php

/**
 * Test: Nette\Forms\Controls\BaseControl & enum
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


test('enum value validation', function () {
	ob_start();
	Form::initialize(true);
	$form = new Form;
	$input = $form->addText('text');
	$input->setValue(TestEnum::Case1->value);

	Assert::true(Validator::validateEqual($input, TestEnum::Case1));
	Assert::true(Validator::validateEqual($input, 'case 1'));
	Assert::false(Validator::validateEqual($input, TestEnum::Case2));
	Assert::false(Validator::validateEqual($input, 1));
});


test('setting enum defaults in selects', function () {
	$items = ['case 1' => '1', 'case 2' => '2', 'case 3' => '3', 'case 4' => '4'];

	ob_start();
	Form::initialize(true);
	$form = new Form;
	$form->addSelect('select', null, $items);
	$form->addMultiSelect('multi', null, $items);
	$form->addHidden('hidden', TestEnum::Case2);

	$form->setDefaults([
		'select' => TestEnum::Case1,
		'multi' => [TestEnum::Case1, TestEnum::Case2],
	]);

	Assert::same([
		'select' => 'case 1',
		'multi' => ['case 1', 'case 2'],
		'hidden' => 'case 2',
	], $form->getValues('array'));
});
