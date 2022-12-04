<?php

/**
 * Test: Nette\Forms\Controls\SelectBox::isOk()
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Validator;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form;
$select = $form->addSelect('foo', null, ['bar' => 'Bar']);

Assert::false($select->isOk());

$select->setDisabled(true);
Assert::true($select->isOk());
$select->setDisabled(false);

$select->setPrompt('Empty');
Assert::true($select->isOk());
$select->setPrompt(false);

$select->setValue('bar');
Assert::true($select->isOk());
$select->setValue(null);

$select->setItems([]);
Assert::true($select->isOk());
$select->setItems(['bar' => 'Bar']);

$select->getControlPrototype()->size = 2;
Assert::true($select->isOk());
$select->getControlPrototype()->size = 1;
Assert::false($select->isOk());


// error message is processed via Rules
$_SERVER['REQUEST_METHOD'] = 'POST';
$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';
Form::initialize(true);
Validator::$messages[Nette\Forms\Controls\SelectBox::Valid] = 'SelectBox "%label" must be filled.';
$form = new Form;
$form->addSelect('foo', 'Foo', ['bar' => 'Bar']);
$form->onSuccess[] = function () {};
$form->fireEvents();
Assert::same(['SelectBox "Foo" must be filled.'], $form->getErrors());
