<?php

/**
 * @phpVersion 8.1
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

enum TestEnum: string {
	case CASE_1 = 'case 1';
	case CASE_2 = 'case 2';
	case CASE_3 = 'case 3';
	case CASE_4 = 'case 4';
}

function createForm(): Form
{
	Form::initialize(true);

	$form = new Form;
	$form->addSelect('select_box', 'label S', ['case 1' => 'Case 1', 'case 2' => 'Case 2', 'case 3' => 'Case 3', 'case 4' => 'Case 4']);
	$form->addMultiSelect('multiselect_box', 'label MS', ['case 1' => 'Case 1', 'case 2' => 'Case 2', 'case 3' => 'Case 3', 'case 4' => 'Case 4']);
	$form->addHidden('hidden_field', TestEnum::CASE_3);

	return $form;
}


test('setDefaults() + array', function () {
	$form = createForm();
	Assert::false($form->isSubmitted());

	$form->setDefaults([
		'select_box' => TestEnum::CASE_1,
		'multiselect_box' => [TestEnum::CASE_1, TestEnum::CASE_3]
	]);

	Assert::same([
		'select_box' => 'case 1',
		'multiselect_box' => ['case 1', 'case 3'],
		'hidden_field' => 'case 3',
	], $form->getValues('array'));
});