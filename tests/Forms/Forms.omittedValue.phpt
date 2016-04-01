<?php

/**
 * Test: Nette\Forms ignored input.
 */

use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form('name');
$form->addProtection();
$form->addText('input');
$form->addText('omittedInput')
	->setOmitted();

Assert::same(['input' => ''], $form->getValues(TRUE));


$form = new Form('name');
$form->addText('input')
	->setDisabled()->setDisabled(FALSE);

Assert::same(['input' => ''], $form->getValues(TRUE));

$form = new Form('name');
$form->addText('input')
	->setDisabled()->setOmitted(FALSE);

Assert::same(['input' => ''], $form->getValues(TRUE));

$form = new Form('name');
$form->addText('input')
	->setDisabled();

Assert::same([], $form->getValues(TRUE));
