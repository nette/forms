<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Rules;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('static condition with false branch', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition(false)
		->setRequired();

	$rules = $input->getRules();
	$items = iterator_to_array($rules);
	Assert::count(1, $items);
	Assert::same(':static', $items[0]->validator);
	Assert::false($items[0]->arg);
	Assert::type(Rules::class, $items[0]->branch);

	Assert::true($rules->validate());
});


test('static condition with true branch', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition(true)
		->setRequired();

	$rules = $input->getRules();
	$items = iterator_to_array($rules);
	Assert::count(1, $items);
	Assert::same(':static', $items[0]->validator);
	Assert::true($items[0]->arg);
	Assert::type(Rules::class, $items[0]->branch);

	Assert::false($rules->validate());
	Assert::same(['This field is required.'], $input->getErrors());
});
