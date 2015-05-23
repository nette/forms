<?php

/**
 * Test: Nette\Forms validation of range depends on another control.
 */

use Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$datasets = [
	[['min' => '10', 'max' => '20', 'value' => 5], FALSE],
	[['min' => '10', 'max' => '20', 'value' => 15], TRUE],
	[['min' => '10', 'max' => '', 'value' => 15], TRUE],
	[['min' => '10', 'max' => '', 'value' => 5], FALSE],
];

foreach ($datasets as $case) {

	$form = new Form;

	$form->addText('min');
	$form->addText('max');
	$form->addText('value')->addRule(Form::RANGE, NULL, [$form['min'], $form['max']]);
	$form->setValues($case[0]);

	Assert::equal($case[1], $form->isValid());
}
