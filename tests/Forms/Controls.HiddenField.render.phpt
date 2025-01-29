<?php

/**
 * Test: Nette\Forms\Controls\HiddenField.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('basic hidden input rendering', function () {
	$form = new Form;
	$input = $form->addHidden('hidden', 'value');

	Assert::null($input->getLabel());
	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="hidden" name="hidden" value="value">', (string) $input->getControl());
});


test('required hidden input remains empty', function () {
	$form = new Form;
	$input = $form->addHidden('hidden')->setRequired('required');

	Assert::same('<input type="hidden" name="hidden" value="">', (string) $input->getControl());
});


test('hidden input within container', function () {
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addHidden('hidden');

	Assert::same('<input type="hidden" name="container[hidden]" value="">', (string) $input->getControl());
});


test('hidden input with HTML ID', function () {
	$form = new Form;
	$input = $form->addHidden('hidden')->setRequired('required');
	$input->setHtmlId($input->getHtmlId());

	Assert::same('<input type="hidden" name="hidden" id="frm-hidden" value="">', (string) $input->getControl());
});


test('hidden input options after rendering', function () {
	$form = new Form;
	$input = $form->addHidden('hidden');

	Assert::same('hidden', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});


test('dateTime object value formatting', function () {
	$form = new Form;
	$input = $form->addHidden('hidden')
		->setValue(new Nette\Utils\DateTime('2013-07-05'));

	Assert::same('<input type="hidden" name="hidden" value="2013-07-05 00:00:00">', (string) $input->getControl());
});
