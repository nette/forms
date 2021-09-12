<?php

/**
 * Test: Nette\Forms ignored input.
 */

declare(strict_types=1);

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form('name');
$form->addProtection();
$form->addText('input');
$form->addText('omittedInput')
	->setOmitted();

Assert::same(['input' => ''], $form->getValues('array'));


Assert::true((new TextInput)->setDisabled()->isOmitted());
Assert::false((new TextInput)->setDisabled()->setDisabled(false)->isOmitted());
Assert::false((new TextInput)->setDisabled()->setOmitted(false)->isOmitted());
