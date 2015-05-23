<?php

/**
 * Test: Nette\Forms user validator.
 */

use Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$datasets = [
	[11, ['Value 11 is not allowed!']],
	[22, []],
	[1, ['Value 22 is required!']],
];

function myValidator1($item, $arg)
{
	return $item->getValue() != $arg;
}


foreach ($datasets as $case) {

	$form = new Form;
	$control = $form->addText('value', 'Value:')
		->addRule('myValidator1', 'Value %d is not allowed!', 11)
		->addRule(~'myValidator1', 'Value %d is required!', 22);

	$control->setValue($case[0])->validate();
	Assert::same($case[1], $control->getErrors());
}
