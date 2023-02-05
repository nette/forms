<?php

declare(strict_types=1);

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


testException('', function () {
	$input = new TextInput('name');
	$input->getHtmlId();
}, Nette\InvalidStateException::class, "Component %a% is not attached to 'Nette\\Forms\\Form'.");


testException('', function () {
	$container = new Nette\Forms\Container;
	$container->setParent(null, 'second');
	$input = $container->addText('name');
	$input->getHtmlId();
}, Nette\InvalidStateException::class, "Component 'name' is not attached to 'Nette\\Forms\\Form'.");


test('', function () {
	$form = new Form;
	$container = $form->addContainer('second');
	$input = $container->addText('name');
	Assert::same('frm-second-name', $input->getHtmlId());
});


test('', function () {
	$form = new Form;
	$input = $form->addText('name');
	Assert::same('frm-name', $input->getHtmlId());
});


test('', function () {
	$form = new Form;
	$input = $form->addText('name');
	Assert::same('frm-name', $input->getHtmlId());
});


test('', function () {
	$form = new Form('signForm');
	$input = $form->addText('name');
	Assert::same('frm-signForm-name', $input->getHtmlId());
});
