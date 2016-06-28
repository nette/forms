<?php

/**
 * Test: Nette\Forms\Rules.
 */

use Nette\Forms\Form;
use Nette\Forms\Rule;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () { // BaseControl
	$form = new Form;
	$input = $form->addText('text');

	Assert::false($input->isRequired());
	Assert::same($input, $input->setRequired());
	Assert::true($input->isRequired());
});


test(function () { // Rules
	$form = new Form;
	$input = $form->addText('text');
	$rules = $input->getRules();

	Assert::false($rules->isRequired());
	Assert::same($rules, $rules->setRequired());
	Assert::true($rules->isRequired());

	$items = iterator_to_array($rules);
	Assert::count(1, $items);
	Assert::same(Form::REQUIRED, $items[0]->validator);
	Assert::null($items[0]->branch);
	Assert::false($items[0]->isNegative);

	Assert::false($rules->validate());
	Assert::same(['This field is required.'], $input->getErrors());
});


test(function () { // 'required' is always the first rule
	$form = new Form;
	$input = $form->addText('text');
	$rules = $input->getRules();

	$rules->addRule($form::EMAIL);
	$rules->addRule($form::REQUIRED);

	$items = iterator_to_array($rules);
	Assert::count(2, $items);
	Assert::same(Form::REQUIRED, $items[0]->validator);
	Assert::same(Form::EMAIL, $items[1]->validator);

	@$rules->addRule(~$form::REQUIRED); // @ - negative rules are deprecated
	$items = iterator_to_array($rules);
	Assert::count(3, $items);
	Assert::same(Form::BLANK, $items[2]->validator);
	Assert::false($items[2]->isNegative);

	Assert::false($rules->validate());
	Assert::same(['This field is required.'], $input->getErrors());
});


test(function () { // setRequired(FALSE)
	$form = new Form;
	$input = $form->addText('text');
	$rules = $input->getRules();

	$rules->addRule($form::EMAIL);
	$rules->addRule($form::REQUIRED);
	$rules->setRequired(FALSE);

	$items = iterator_to_array($rules);
	Assert::count(1, $items);
	Assert::same(Form::EMAIL, $items[0]->validator);

	Assert::true($rules->validate());
	Assert::same([], $input->getErrors());
});


test(function () { // addRule(~Form::REQUIRED)
	$form = new Form;
	$input = $form->addText('text');

	Assert::false($input->isRequired());
	Assert::same($input, @$input->addRule(~Form::REQUIRED)); // @ - negative rules are deprecated
	Assert::false($input->isRequired());
});
