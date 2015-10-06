<?php

/**
 * Test: Nette\Forms\ControlGroup.
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

function getContainer() {
	$container = new \Nette\Forms\Container();
	$container->addText('street', 'Street');
	$container->addText('town', 'Town');
	return $container;
}

$form = new Nette\Forms\Form;


// controls only
$group1 = $form->addGroup();
$form->addText('name', 'Name');
$form->addText('surname', 'Surname');

Assert::same($group1, $form->getGroup(0));
Assert::true($form->getGroup(0) instanceof \Nette\Forms\ControlGroup);
Assert::equal(2, count($group1->getControls()));
Assert::equal(array($form['name'], $form['surname']), $group1->getControls());


// container only
$group2 = $form->addGroup('Address');
$form->addComponent(getContainer(), 'address');

Assert::same($group2, $form->getGroup('Address'));
Assert::true($form->getGroup('Address') instanceof \Nette\Forms\ControlGroup);
Assert::equal(2, count($group2->getControls()));
Assert::equal(array($form['address']['street'], $form['address']['town']), $group2->getControls());


// nested containers
$group3 = $form->addGroup('Nested');
$container = getContainer();
$container->addComponent(getContainer(), 'child');
$form->addComponent($container, 'parent');

Assert::equal(4, count($group3->getControls()));
Assert::equal(array($form['parent']['street'], $form['parent']['town'], $form['parent']['child']['street'], $form['parent']['child']['town']), $group3->getControls());


// container and controls
$group4 = $form->addGroup('Mix');
$form->addText('foo');
$form->addComponent(getContainer(), 'mix');
$form->addText('bar');

Assert::equal(4, count($group4->getControls()));
Assert::equal(array($form['foo'], $form['mix']['street'], $form['mix']['town'], $form['bar']), $group4->getControls());


Assert::equal(4, count($form->getGroups()));
