<?php

/**
 * Test: Nette\Forms validation of range depends on another control.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$datasets = [
	[['min' => '10', 'max' => '20', 'value' => 5], false],
	[['min' => '10', 'max' => '20', 'value' => 15], true],
	[['min' => '10', 'max' => '', 'value' => 15], true],
	[['min' => '10', 'max' => '', 'value' => 5], false],
];

foreach ($datasets as $case) {
	$form = new Form;

	$form->addText('min');
	$form->addText('max');
	$form->addText('value')->addRule(Form::Range, null, [$form['min'], $form['max']]);
	$form->setValues($case[0]);

	Assert::equal($case[1], $form->isValid());
}
