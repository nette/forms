<?php

/**
 * Test: Nette\Forms\ControlGroup.
 */

use Tester\Assert;
use Nette\Forms\ControlGroup;

require __DIR__ . '/../bootstrap.php';


function createContainer() {
	$container = new Nette\Forms\Container;
	$container->addText('street', 'Street');
	$container->addText('town', 'Town');
	return $container;
}

$form = new Nette\Forms\Form;


// controls only
$group = $form->addGroup();
$form->addText('name', 'Name');
$form->addText('surname', 'Surname');

Assert::same($group, $form->getGroup(0));
Assert::true($form->getGroup(0) instanceof ControlGroup);
Assert::equal(2, count($group->getControls()));
Assert::equal(array($form['name'], $form['surname']), $group->getControls());
