<?php

/**
 * Test: Nette\Forms\Controls\MultiSelectBox.
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


test(function () {
	$form = new Form;
	$input = $form->addMultiSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	]);

	Assert::type(Html::class, $input->getLabel());
	Assert::same('<label for="frm-list">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-list">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type(Html::class, $input->getControl());
	Assert::same('<select name="list[]" id="frm-list" multiple><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());
});


test(function () { // selected
	$form = new Form;
	$input = $form->addMultiSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setValue(0);

	Assert::same('<select name="list[]" id="frm-list" multiple><option value="a">First</option><option value="0" selected>Second</option></select>', (string) $input->getControl());
});


test(function () { // selected 2x
	$form = new Form;
	$input = $form->addMultiSelect('list', 'Label', [
		['a' => 'First'],
		['a' => 'First'],
	])->setValue('a');

	Assert::same('<select name="list[]" id="frm-list" multiple><optgroup label="0"><option value="a" selected>First</option></optgroup><optgroup label="1"><option value="a" selected>First</option></optgroup></select>', (string) $input->getControl());
});


test(function () { // translator & groups
	$form = new Form;
	$input = $form->addMultiSelect('list', 'Label', [
		'a' => 'First',
		'group' => ['Second', 'Third'],
	]);
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-list">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-list">Another label</label>', (string) $input->getLabel('Another label'));
	Assert::same('<select name="list[]" id="frm-list" multiple><option value="a">FIRST</option><optgroup label="GROUP"><option value="0">SECOND</option><option value="1">THIRD</option></optgroup></select>', (string) $input->getControl());
});


test(function () { // Html with translator & groups
	$form = new Form;
	$input = $form->addMultiSelect('list', Html::el('b', 'Label'), [
		'a' => Html::el('option', 'First')->class('class'),
		'group' => [Html::el('option', 'Second')],
	]);
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-list"><b>Label</b></label>', (string) $input->getLabel());
	Assert::same('<label for="frm-list"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
	Assert::same('<select name="list[]" id="frm-list" multiple><option class="class" value="a">First</option><optgroup label="GROUP"><option value="0">Second</option></optgroup></select>', (string) $input->getControl());
});


test(function () { // validation rules
	$form = new Form;
	$input = $form->addMultiSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setRequired('required');

	Assert::same('<select name="list[]" id="frm-list" required data-nette-rules=\'[{"op":":filled","msg":"required"}]\' multiple><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());
});


test(function () { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addMultiSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	]);

	Assert::same('<select name="container[list][]" id="frm-container-list" multiple><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());
});


test(function () { // disabled all
	$form = new Form;
	$input = $form->addMultiSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setDisabled(true);

	Assert::same('<select name="list[]" id="frm-list" disabled multiple><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());
});


test(function () { // disabled one
	$form = new Form;
	$input = $form->addMultiSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setDisabled(['a']);

	Assert::same('<select name="list[]" id="frm-list" multiple><option value="a" disabled>First</option><option value="0">Second</option></select>', (string) $input->getControl());
});


test(function () { // rendering options
	$form = new Form;
	$input = $form->addMultiSelect('list');

	Assert::same('select', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});


test(function () {
	$form = new Form;
	$input = $form->addMultiSelect('list', 'Label', [
		1 => 'First',
		2 => 'Second',
	])->setValue(1);
	$input->addOptionAttributes(['bar' => 'b', 'selected?' => 2, 'foo:' => [1 => 'a', 2 => 'b']]);
	$input->addOptionAttributes(['bar' => 'c']);
	Assert::same('<select name="list[]" id="frm-list" multiple><option bar="c" value="1" selected foo="a">First</option><option bar="c" value="2" foo="b">Second</option></select>', (string) $input->getControl());
});
