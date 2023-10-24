<?php

/**
 * Test: Nette\Forms\Controls\HiddenField.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('', function () {
	$form = new Form;
	$input = $form->addHidden('hidden', 'value');

	Assert::null($input->getLabel());
	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="hidden" name="hidden" value="value">', (string) $input->getControl());
});


test('no validation rules', function () {
	$form = new Form;
	$input = $form->addHidden('hidden')->setRequired('required');

	Assert::same('<input type="hidden" name="hidden" value="">', (string) $input->getControl());
});


test('container', function () {
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addHidden('hidden');

	Assert::same('<input type="hidden" name="container[hidden]" value="">', (string) $input->getControl());
});


test('forced ID', function () {
	$form = new Form;
	$input = $form->addHidden('hidden')->setRequired('required');
	$input->setHtmlId($input->getHtmlId());

	Assert::same('<input type="hidden" name="hidden" id="frm-hidden" value="">', (string) $input->getControl());
});


test('rendering options', function () {
	$form = new Form;
	$input = $form->addHidden('hidden');

	Assert::same('hidden', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});


test('object', function () {
	$form = new Form;
	$input = $form->addHidden('hidden')
		->setValue(new Nette\Utils\DateTime('2013-07-05'));

	Assert::same('<input type="hidden" name="hidden" value="2013-07-05 00:00:00">', (string) $input->getControl());
});
