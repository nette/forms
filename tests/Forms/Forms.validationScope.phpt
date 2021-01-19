<?php

/**
 * Test: Nette\Forms validation scope.
 */

declare(strict_types=1);

use Nette\Forms\Container;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

//Tracy\Debugger::enable();
$datasets = [
	['send1', ['container', 'form', 'name', 'age', 'age2'], null],
	['send2', ['form'], ['optional' => '', 'details' => []]],
	['send3', ['form', 'name'], null],
	['send4', ['form', 'age'], null],
	['send5', ['container', 'form', 'age', 'age2'], null],
];

foreach ($datasets as $case) {
	$form = new Form;
	$form->onValidate[] = function (Form $form, ?array $values) use (&$values1) {
		$form->addError('form');
		$values1 = $values;
	};
	$form->addText('name')->setRequired('name');
	$form->addText('optional');

	$details = $form->addContainer('details');
	$details->onValidate[] = function (Container $container, $values) use (&$values2) {
		$container->getForm()->addError('container');
		$values2 = $values;
	};
	$details->addText('age')->setRequired('age');
	$details->addText('age2')->setRequired('age2');

	$form->addSubmit('send1');
	$form->addSubmit('send2')->setValidationScope([$form['optional']]);
	$form->addSubmit('send3')->setValidationScope([$form['name']]);
	$form->addSubmit('send4')->setValidationScope([$form['details']['age']]);
	$form->addSubmit('send5')->setValidationScope([$form['details']]);

	$form->setSubmittedBy($form[$case[0]]);

	Assert::truthy($form->isSubmitted());
	$form->validate();
	Assert::equal($case[1], $form->getErrors());

	Assert::same($case[2], $values1);
	Assert::null($values2);
}
