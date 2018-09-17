<?php

/**
 * Test: Nette\Forms\Controls\SelectBox::setDefaultValueAuto()
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form;
$select = $form->addSelect('foo', null, ['bar' => 'Bar', 'foo' => 'Foo']);
Assert::same('bar', $select->getValue());

$select->setPrompt('Baz');
Assert::null($select->getValue());

$select->setDefaultValue('foo');
Assert::same('foo', $select->getValue());


$form = new Form;
$select = $form->addSelect('foo')
	->setPrompt('Baz')
	->setItems(['bar' => 'Bar', 'foo' => 'Foo']);
Assert::null($select->getValue());


$form = new Form;
$select = $form->addSelect('foo')
	->setItems(['bar' => 'Bar', 'foo' => 'Foo'])
	->setDefaultValue('foo')
	->setPrompt('Baz');
Assert::same('foo', $select->getValue());


$form = new Form;
$select = $form->addSelect('foo')
	->setPrompt('Baz')
	->setItems(['bar' => 'Bar', 'foo' => 'Foo'])
	->setDefaultValue('foo');
Assert::same('foo', $select->getValue());
