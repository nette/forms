<?php

/**
 * Test: Nette\Forms\Helpers::exportRules()
 */

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\Forms\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addRule(Form::FILLED, NULL, []);
	Assert::same([
		[
			'op' => ':filled',
			'msg' => 'This field is required.',
			'arg' => [],
		],
	], Helpers::exportRules($input->getRules()));
});


test(function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addRule(Form::EMAIL);
	Assert::same([
		['op' => ':email', 'msg' => 'Please enter a valid email address.']
	], Helpers::exportRules($input->getRules()));
});


test(function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->setRequired(FALSE);
	$input->addRule(Form::EMAIL);
	Assert::same([
		['op' => 'optional'],
		['op' => ':email', 'msg' => 'Please enter a valid email address.'],
	], Helpers::exportRules($input->getRules()));
});


test(function () {
	$form = new Form;
	$input1 = $form->addText('text1');
	$input2 = $form->addText('text2');
	$input2->setRequired(FALSE);
	$input2->addConditionOn($input1, Form::EMAIL)
		->setRequired(TRUE)
		->addRule($form::EMAIL);
	$input2->addConditionOn($input1, Form::INTEGER)
		->setRequired(FALSE)
		->addRule($form::EMAIL);

	Assert::same([
		['op' => 'optional'],
		[
			'op' => ':email',
			'rules' => [
				['op' => ':filled', 'msg' => 'This field is required.'],
				['op' => ':email', 'msg' => 'Please enter a valid email address.'],
			],
			'control' => 'text1',
		],
		[
			'op' => ':integer',
			'rules' => [
				['op' => 'optional'],
				['op' => ':email', 'msg' => 'Please enter a valid email address.'],
			],
			'control' => 'text1',
		],
	], Helpers::exportRules($input2->getRules()));
});
