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
Assert::equal([$form['name'], $form['surname']], $group->getControls());


// container only
$group = $form->addGroup('Address');
$form->addComponent(createContainer(), 'address');

Assert::same($group, $form->getGroup('Address'));
Assert::true($form->getGroup('Address') instanceof ControlGroup);
Assert::equal(2, count($group->getControls()));
Assert::equal([$form['address']['street'], $form['address']['town']], $group->getControls());


// nested containers
$group = $form->addGroup('Nested');
$container = createContainer();
$container->addComponent(createContainer(), 'child');
$form->addComponent($container, 'parent');

Assert::equal(4, count($group->getControls()));
Assert::equal([$form['parent']['street'], $form['parent']['town'], $form['parent']['child']['street'], $form['parent']['child']['town']], $group->getControls());


// container and controls
$group = $form->addGroup('Mix');
$form->addText('foo');
$form->addComponent(createContainer(), 'mix');
$form->addText('bar');

Assert::equal(4, count($group->getControls()));
Assert::equal([$form['foo'], $form['mix']['street'], $form['mix']['town'], $form['bar']], $group->getControls());


// addContainer
$group = $form->addGroup('Container');
$container = $form->addContainer('container');
$container->addText('text1');
$container->addText('text2');

Assert::equal(2, count($group->getControls()));
Assert::equal([$form['container']['text1'], $form['container']['text2']], $group->getControls());


// addContainer recursive
$group = $form->addGroup('Container');
$container1 = $form->addContainer('outer');
$container2 = $container1->addContainer('inner');
$container2->addText('text');

Assert::equal(1, count($group->getControls()));
Assert::equal([$form['outer']['inner']['text']], $group->getControls());

Assert::equal(6, count($form->getGroups()));
