<?php

/**
 * Test: Nette\Forms\Controls\RadioList.
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


test('radio list rendering basics', function () {
	$form = new Form;
	$input = $form->addRadioList('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	]);

	Assert::type(Html::class, $input->getLabel());
	Assert::same('<label>Label</label>', (string) $input->getLabel());
	Assert::same('<label>Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type(Html::class, $input->getLabelPart(0));
	Assert::same('<label for="frm-list-0">Second</label>', (string) $input->getLabelPart(0));

	Assert::type(Html::class, $input->getControl());
	Assert::same('<label><input type="radio" name="list" value="a">First</label><br><label><input type="radio" name="list" value="0">Second</label>', (string) $input->getControl());

	Assert::type(Html::class, $input->getControlPart(0));
	Assert::same('<input type="radio" name="list" id="frm-list-0" value="0">', (string) $input->getControlPart(0));
});


test('selected radio button', function () {
	$form = new Form;
	$input = $form->addRadioList('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setValue(0);

	Assert::same('<label><input type="radio" name="list" value="a">First</label><br><label><input type="radio" name="list" checked value="0">Second</label>', (string) $input->getControl());
});


test('translating radio options', function () {
	$form = new Form;
	$input = $form->addRadioList('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	]);
	$input->setTranslator(new Translator);

	Assert::same('<label>Label</label>', (string) $input->getLabel());
	Assert::same('<label>Another label</label>', (string) $input->getLabel('Another label'));
	Assert::same('<label for="frm-list-0">SECOND</label>', (string) $input->getLabelPart(0));

	Assert::same('<label><input type="radio" name="list" value="a">FIRST</label><br><label><input type="radio" name="list" value="0">SECOND</label>', (string) $input->getControl());
	Assert::same('<input type="radio" name="list" id="frm-list-0" value="0">', (string) $input->getControlPart(0));
});


test('HTML in radio labels', function () {
	$form = new Form;
	$input = $form->addRadioList('list', Html::el('b', 'Label'), [
		'a' => Html::el('b', 'First'),
	]);
	$input->setTranslator(new Translator);

	Assert::same('<label><b>Label</b></label>', (string) $input->getLabel());
	Assert::same('<label><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));

	Assert::same('<label><input type="radio" name="list" value="a"><b>First</b></label>', (string) $input->getControl());
	Assert::same('<input type="radio" name="list" id="frm-list-a" value="a">', (string) $input->getControlPart('a'));
});


test('required radio list', function () {
	$form = new Form;
	$input = $form->addRadioList('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setRequired('required');

	Assert::same('<label><input type="radio" name="list" required data-nette-rules=\'[{"op":":filled","msg":"required"}]\' value="a">First</label><br><label><input type="radio" name="list" required value="0">Second</label>', (string) $input->getControl());
	Assert::same('<input type="radio" name="list" id="frm-list-0" required data-nette-rules=\'[{"op":":filled","msg":"required"}]\' value="0">', (string) $input->getControlPart(0));
});


test('container naming in radio', function () {
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addRadioList('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	]);

	Assert::same('<label><input type="radio" name="container[list]" value="a">First</label><br><label><input type="radio" name="container[list]" value="0">Second</label>', (string) $input->getControl());
});


test('custom separators and containers', function () {
	$form = new Form;
	$input = $form->addRadioList('list', null, [
		'a' => 'b',
	]);
	$input->getSeparatorPrototype()->setName('hr');
	$input->getContainerPrototype()->setName('div');

	Assert::same('<div><label><input type="radio" name="list" value="a">b</label></div>', (string) $input->getControl());
});


test('disabled radio list', function () {
	$form = new Form;
	$input = $form->addRadioList('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setDisabled(true);

	Assert::same('<label><input type="radio" name="list" disabled value="a">First</label><br><label><input type="radio" name="list" disabled value="0">Second</label>', (string) $input->getControl());
});


test('disabled radio options', function () {
	$form = new Form;
	$input = $form->addRadioList('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setDisabled(['a']);

	Assert::same('<label><input type="radio" name="list" disabled value="a">First</label><br><label><input type="radio" name="list" value="0">Second</label>', (string) $input->getControl());
	Assert::same('<input type="radio" name="list" id="frm-list-a" disabled value="a">', (string) $input->getControlPart('a'));
});


test('label prototype styling', function () {
	$form = new Form;
	$input = $form->addRadioList('list', null, [
		'a' => 'b',
	]);
	$input->getItemLabelPrototype()->class('foo');

	Assert::same('<label></label>', (string) $input->getLabel());
	Assert::same('<label class="foo" for="frm-list-a">b</label>', (string) $input->getLabelPart('a'));
	Assert::same('<label class="foo"><input type="radio" name="list" value="a">b</label>', (string) $input->getControl());
});


test('auto-generated IDs', function () {
	$form = new Form;
	$input = $form->addRadioList('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	]);
	$input->generateId = true;

	Assert::same('<label for="frm-list-a"><input type="radio" name="list" id="frm-list-a" value="a">First</label><br><label for="frm-list-0"><input type="radio" name="list" id="frm-list-0" value="0">Second</label>', (string) $input->getControl());
});


test('default value handling', function () {
	$form = new Form;
	$input = $form->addRadioList('list', 'Label', [
		1 => 'First',
		2 => 'Second',
	])->setDefaultValue(1);

	Assert::same('<input type="radio" name="list" id="frm-list-1" checked value="1">', (string) $input->getControlPart('1'));
});


test('radio list control options', function () {
	$form = new Form;
	$input = $form->addRadioList('list');

	Assert::same('radio', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});
