<?php

/**
 * Test: Nette\Forms validation scope.
 */

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$datasets = [
	['send1', ['name', 'age', 'age2']],
	['send2', []],
	['send3', ['name']],
	['send4', ['age']],
	['send5', ['age', 'age2']],
];

foreach ($datasets as $case) {

	$form = new Form;
	$form->addText('name')->setRequired('name');

	$details = $form->addContainer('details');
	$details->addText('age')->setRequired('age');
	$details->addText('age2')->setRequired('age2');

	$form->addSubmit('send1');
	$form->addSubmit('send2')->setValidationScope(FALSE);
	$form->addSubmit('send3')->setValidationScope([$form['name']]);
	$form->addSubmit('send4')->setValidationScope([$form['details']['age']]);
	$form->addSubmit('send5')->setValidationScope([$form['details']]);

	$form->setSubmittedBy($form[$case[0]]);

	Assert::truthy($form->isSubmitted());
	$form->validate();
	Assert::equal($case[1], $form->getErrors());

}
