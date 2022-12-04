<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('', function () {
	$form = new Form;
	$input = $form->addText('text1');
	$input->setRequired();
	$input->addRule($form::Email);

	$rules = iterator_to_array($input->getRules());
	Assert::count(2, $rules);
	Assert::same($form::Required, $rules[0]->validator);
	Assert::same($form::Email, $rules[1]->validator);

	$input->getRules()->removeRule($form::Email);

	$rules = iterator_to_array($input->getRules());
	Assert::count(1, $rules);
	Assert::same($form::Required, $rules[0]->validator);

	$input->getRules()->removeRule($form::Required);

	$rules = iterator_to_array($input->getRules());
	Assert::count(0, $rules);
});


test('', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->setRequired();

	Assert::count(1, iterator_to_array($input->getRules()));
	$input->getRules()->removeRule($form::Email);

	Assert::count(1, iterator_to_array($input->getRules()));
});


test('', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::Email);

	Assert::count(1, iterator_to_array($input->getRules()));
	$input->getRules()->removeRule($form::Email);

	Assert::count(1, iterator_to_array($input->getRules()));
});
