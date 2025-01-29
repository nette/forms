<?php

/**
 * Test: Nette\Forms\Controls\Checkbox.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Translator implements Nette\Localization\ITranslator
{
	public function translate($s, ...$parameters): string
	{
		return strtoupper($s);
	}
}


test('checkbox label and control structure', function () {
	$form = new Form;
	$input = $form->addCheckbox('on', 'Label');

	Assert::null($input->getLabel());

	Assert::type(Html::class, $input->getControl());
	Assert::same('<label for="frm-on"><input type="checkbox" name="on" id="frm-on">Label</label>', (string) $input->getControl());

	Assert::type(Html::class, $input->getLabelPart());
	Assert::same('<label for="frm-on">Label</label>', (string) $input->getLabelPart());

	Assert::type(Html::class, $input->getControlPart());
	Assert::same('<input type="checkbox" name="on" id="frm-on">', (string) $input->getControlPart());

	$input->setValue(true);
	Assert::same('<label for="frm-on"><input type="checkbox" name="on" id="frm-on" checked>Label</label>', (string) $input->getControl());
	Assert::same('<input type="checkbox" name="on" id="frm-on" checked>', (string) $input->getControlPart());
});


test('translator does not affect checkbox label', function () {
	$form = new Form;
	$input = $form->addCheckbox('on', 'Label');
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-on"><input type="checkbox" name="on" id="frm-on">Label</label>', (string) $input->getControl());
});


test('required checkbox attributes', function () {
	$form = new Form;
	$input = $form->addCheckbox('on')->setRequired('required');

	Assert::same('<label for="frm-on"><input type="checkbox" name="on" id="frm-on" required data-nette-rules=\'[{"op":":filled","msg":"required"}]\'></label>', (string) $input->getControl());
});


test('checkbox within container naming', function () {
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addCheckbox('on');

	Assert::same('<label for="frm-container-on"><input type="checkbox" name="container[on]" id="frm-container-on"></label>', (string) $input->getControl());
});


test('checkbox options after rendering', function () {
	$form = new Form;
	$input = $form->addCheckbox('on');

	Assert::same('checkbox', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});
