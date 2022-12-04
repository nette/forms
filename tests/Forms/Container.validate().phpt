<?php

/**
 * Test: Nette\Forms\Container::validate().
 */

declare(strict_types=1);

use Nette\Forms\Container;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

$form = new Form;
$form->addText('name')->addRule($form::Integer);

$form->onValidate[] = function (Container $container) {
	$container['name']->addError('fail 1');
};

$container = $form->addContainer('cont');
$container->addText('name');
$container->onValidate[] = function (Container $container) {
	$container['name']->addError('fail 2');
};

$form->setValues(['name' => 'invalid*input']);
$form->validate();

Assert::same([
	'Please enter a valid integer.',
	'fail 1',
	'fail 2',
], $form->getErrors());
