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
