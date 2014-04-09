<?php

/**
 * Test: Nette\Forms\Controls\SelectBox::isOk()
 */

use Nette\Forms\Validator;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form;
$select = $form->addSelect('foo', NULL, ['bar' => 'Bar']);

Assert::false($select->isOk());

$select->setDisabled(TRUE);
Assert::true($select->isOk());
$select->setDisabled(FALSE);

$select->setPrompt('Empty');
Assert::true($select->isOk());
$select->setPrompt(FALSE);

$select->setValue('bar');
Assert::true($select->isOk());
$select->setValue(NULL);

$select->setItems([]);
Assert::true($select->isOk());
$select->setItems(['bar' => 'Bar']);

$select->getControlPrototype()->size = 2;
Assert::true($select->isOk());
$select->getControlPrototype()->size = 1;
Assert::false($select->isOk());


// error message is processed via Rules
$_SERVER['REQUEST_METHOD'] = 'POST';
Validator::$messages[Nette\Forms\Controls\SelectBox::VALID] = 'SelectBox "%label" must be filled.';
$form = new Form;
$form->addSelect('foo', 'Foo', ['bar' => 'Bar']);
$form->fireEvents();
Assert::same(['SelectBox "Foo" must be filled.'], $form->getErrors());
