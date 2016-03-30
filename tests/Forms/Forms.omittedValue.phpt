<?php

/**
 * Test: Nette\Forms ignored input.
 */

use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Nette\Forms\Controls\TextInput;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form('name');
$form->addProtection();
$form->addText('input');
$form->addText('omittedInput')
	->setOmitted();

Assert::same(['input' => ''], $form->getValues(TRUE));


Assert::true((new TextInput)->setDisabled()->isOmitted());
Assert::false((new TextInput)->setDisabled()->setDisabled(FALSE)->isOmitted());
Assert::false((new TextInput)->setDisabled()->setOmitted(FALSE)->isOmitted());
