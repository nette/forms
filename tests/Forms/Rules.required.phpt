<?php

/**
 * Test: Nette\Forms\Rules.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('toggling required status of a field', function () {
	$form = new Form;
	$input = $form->addText('text');

	Assert::false($input->isRequired());
	Assert::same($input, $input->setRequired());
	Assert::true($input->isRequired());
});


test('required rule addition and validation', function () {
	$form = new Form;
	$input = $form->addText('text');
	$rules = $input->getRules();

	Assert::false($rules->isRequired());
	Assert::same($rules, $rules->setRequired());
	Assert::true($rules->isRequired());

	$items = iterator_to_array($rules);
	Assert::count(1, $items);
	Assert::same(Form::Required, $items[0]->validator);
	Assert::null($items[0]->branch);
	Assert::false($items[0]->isNegative);

	Assert::false($rules->validate());
	Assert::same(['This field is required.'], $input->getErrors());
});


test('combining required with other validations', function () {
	$form = new Form;
	$input = $form->addText('text');
	$rules = $input->getRules();

	$rules->addRule($form::Email);
	$rules->addRule($form::Required);

	$items = iterator_to_array($rules);
	Assert::count(2, $items);
	Assert::same(Form::Required, $items[0]->validator);
	Assert::same(Form::Email, $items[1]->validator);

	@$rules->addRule(~$form::Required); // @ - negative rules are deprecated
	$items = iterator_to_array($rules);
	Assert::count(3, $items);
	Assert::same(Form::Blank, $items[0]->validator);
	Assert::false($items[0]->isNegative);

	Assert::false($rules->validate());
	Assert::same(['This field is required.'], $input->getErrors());

	$rules->addCondition($form::Blank);
	$items = iterator_to_array($rules);
	Assert::count(4, $items);
	Assert::same(Form::Blank, $items[0]->validator);
	Assert::same(Form::Blank, $items[1]->validator);

	Assert::false($rules->validate());
	Assert::same(['This field is required.'], $input->getErrors());
});


test('disabling required rule post-addition', function () {
	$form = new Form;
	$input = $form->addText('text');
	$rules = $input->getRules();

	$rules->addRule($form::Email);
	$rules->addRule($form::Required);
	$rules->setRequired(false);

	$items = iterator_to_array($rules);
	Assert::count(1, $items);
	Assert::same(Form::Email, $items[0]->validator);

	Assert::true($rules->validate());
	Assert::same([], $input->getErrors());
});


test('conditional required based on checkbox', function () {
	$form = new Form;
	$form->addCheckbox('checkbox');
	$input = $form->addText('text');
	$input->setRequired(false)
		->addRule($form::Email)
		->addConditionOn($form['checkbox'], $form::Equal, false)
			->addRule($form::Required);

	Assert::false($input->getRules()->validate());
	Assert::same(['This field is required.'], $input->getErrors());
});


test('negative required rule handling', function () {
	$form = new Form;
	$input = $form->addText('text');

	Assert::false($input->isRequired());
	Assert::same($input, @$input->addRule(~Form::Required)); // @ - negative rules are deprecated
	Assert::false($input->isRequired());
});
