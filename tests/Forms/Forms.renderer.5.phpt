<?php

/**
 * Test: Nette\Forms default rendering containers inside groups.
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


function createContainer() {
	$container = new Nette\Forms\Container;
	$container->addText('street', 'Street');
	$container->addText('town', 'Town');
	$container->addText('country', 'Country');
	return $container;
}

$form = new Nette\Forms\Form;

// controls only
$form->addGroup();
$form->addText('name', 'Name');
$form->addText('surname', 'Surname');

// container only
$form->addGroup('Address');
$form->addComponent(createContainer(), 'address');

// container + control
$form->addGroup('Shipping Address');
$form->addComponent(createContainer(), 'shippingAddress');
$form->addTextArea('note', 'Note');

// nested containers
$form->addGroup('Nested');
$container = createContainer();
$container->addComponent(createContainer(), 'child');
$form->addComponent($container, 'parent');

$form->setCurrentGroup();
$form->addSubmit('submit', 'Order');

$form->fireEvents();

Assert::matchFile(__DIR__ . '/Forms.renderer.5.expect', $form->__toString(TRUE));
