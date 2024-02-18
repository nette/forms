<?php

/**
 * Test: Nette\Forms validation scope.
 */

declare(strict_types=1);

use Nette\Forms\Container;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$datasets = [
	['send1', ['container', 'form', 'name', 'age', 'age2']],
	['send2', ['form']],
	['send3', ['form', 'name']],
	['send4', ['form', 'age']],
	['send5', ['container', 'form', 'age', 'age2']],
];

foreach ($datasets as $case) {
	$form = new Form;
	$res = [];
	$form->onValidate[] = function (Form $form, array $vals) use (&$res) {
		$form->addError('form');
		$res = $vals;
	};
	$form->addText('name')->setRequired('name');

	$details = $form->addContainer('details');
	$details->onValidate[] = function (Container $container) {
		$container->getForm()->addError('container');
	};
	$details->addText('age')->setRequired('age');
	$details->addText('age2')->setRequired('age2');

	$form->addSubmit('send1');
	$form->addSubmit('send2')->setValidationScope([]);
	$form->addSubmit('send3')->setValidationScope([$form['name']]);
	$form->addSubmit('send4')->setValidationScope(['details-age']);
	$form->addSubmit('send5')->setValidationScope([$form['details']]);

	$form->setSubmittedBy($form[$case[0]]);

	Assert::truthy($form->isSubmitted());
	$form->validate();
	Assert::equal($case[1], $form->getErrors());
	Assert::equal(['name' => '', 'details' => ['age' => '', 'age2' => '']], $res);
}
