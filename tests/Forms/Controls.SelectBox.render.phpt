<?php

/**
 * Test: Nette\Forms\Controls\SelectBox.
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


test('', function () {
	$form = new Form;
	$input = $form->addSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	]);

	Assert::type(Html::class, $input->getLabel());
	Assert::same('<label for="frm-list">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-list">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type(Html::class, $input->getControl());
	Assert::same('<select name="list" id="frm-list"><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());
});


test('selected', function () {
	$form = new Form;
	$input = $form->addSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setValue(0);

	Assert::same('<select name="list" id="frm-list"><option value="a">First</option><option value="0" selected>Second</option></select>', (string) $input->getControl());
});


test('selected 2x', function () {
	$form = new Form;
	$input = $form->addSelect('list', 'Label', [
		['a' => 'First'],
		['a' => 'First'],
	])->setValue('a');

	Assert::same('<select name="list" id="frm-list"><optgroup label="0"><option value="a" selected>First</option></optgroup><optgroup label="1"><option value="a">First</option></optgroup></select>', (string) $input->getControl());
});


test('prompt', function () {
	$form = new Form;
	$input = $form->addSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setPrompt('prompt');

	Assert::same('<select name="list" id="frm-list"><option value="">prompt</option><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());

	$input->setValue(0);

	Assert::same('<select name="list" id="frm-list"><option value="">prompt</option><option value="a">First</option><option value="0" selected>Second</option></select>', (string) $input->getControl());
});


test('prompt + required', function () {
	$form = new Form;
	$input = $form->addSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setPrompt('prompt')->setRequired();

	Assert::same('<select name="list" id="frm-list" required data-nette-rules=\'[{"op":":filled","msg":"This field is required."}]\'><option value="" disabled hidden selected>prompt</option><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());

	$input->setValue(0);

	Assert::same('<select name="list" id="frm-list" required data-nette-rules=\'[{"op":":filled","msg":"This field is required."}]\'><option value="" disabled hidden>prompt</option><option value="a">First</option><option value="0" selected>Second</option></select>', (string) $input->getControl());
});


test('unique prompt', function () {
	$form = new Form;
	$input = $form->addSelect('list', 'Label', [
		'' => 'First',
	])->setPrompt('prompt');

	Assert::same('<select name="list" id="frm-list"><option value="' . "\t" . '">prompt</option><option value="">First</option></select>', (string) $input->getControl());

	$input->setValue('');

	Assert::same('<select name="list" id="frm-list"><option value="' . "\t" . '">prompt</option><option value="" selected>First</option></select>', (string) $input->getControl());
});


test('translator & groups', function () {
	$form = new Form;
	$input = $form->addSelect('list', 'Label', [
		'a' => 'First',
		'group' => ['Second', 'Third'],
	])->setPrompt('Prompt');
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-list">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-list">Another label</label>', (string) $input->getLabel('Another label'));
	Assert::same('<select name="list" id="frm-list"><option value="">PROMPT</option><option value="a">FIRST</option><optgroup label="GROUP"><option value="0">SECOND</option><option value="1">THIRD</option></optgroup></select>', (string) $input->getControl());
});


test('Html with translator & groups', function () {
	$form = new Form;
	$input = $form->addSelect('list', Html::el('b', 'Label'), [
		'a' => Html::el('option', 'First')->class('class'),
		'group' => [Html::el('option', 'Second')],
	])->setPrompt(Html::el('option', 'Prompt')->class('class'));
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-list"><b>Label</b></label>', (string) $input->getLabel());
	Assert::same('<label for="frm-list"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
	Assert::same('<select name="list" id="frm-list"><option class="class" value="">Prompt</option><option class="class" value="a">First</option><optgroup label="GROUP"><option value="0">Second</option></optgroup></select>', (string) $input->getControl());
});


test('validation rules', function () {
	$form = new Form;
	$input = $form->addSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setRequired('required');

	Assert::same('<select name="list" id="frm-list" required data-nette-rules=\'[{"op":":filled","msg":"required"}]\'><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());
});


test('container', function () {
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	]);

	Assert::same('<select name="container[list]" id="frm-container-list"><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());
});


test('disabled all', function () {
	$form = new Form;
	$input = $form->addSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setDisabled(true);

	Assert::same('<select name="list" id="frm-list" disabled><option value="a">First</option><option value="0">Second</option></select>', (string) $input->getControl());
});


test('disabled one', function () {
	$form = new Form;
	$input = $form->addSelect('list', 'Label', [
		'a' => 'First',
		0 => 'Second',
	])->setDisabled(['a']);

	Assert::same('<select name="list" id="frm-list"><option value="a" disabled>First</option><option value="0">Second</option></select>', (string) $input->getControl());
});


test('rendering options', function () {
	$form = new Form;
	$input = $form->addSelect('list');

	Assert::same('select', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});


test('', function () {
	$form = new Form;
	$input = $form->addSelect('list', 'Label', [
		1 => 'First',
		2 => 'Second',
	])->setValue(1);
	$input->addOptionAttributes(['bar' => 'b', 'selected?' => 2]);
	$input->addOptionAttributes(['bar' => 'c']);
	$input->setOptionAttribute('foo:', [1 => 'a', 2 => 'b']);
	Assert::same('<select name="list" id="frm-list"><option bar="c" value="1" selected foo="a">First</option><option bar="c" value="2" foo="b">Second</option></select>', (string) $input->getControl());
});
