<?php

/**
 * Test: Nette\Forms\Controls\TextArea.
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


test('basic textarea rendering', function () {
	$form = new Form;
	$input = $form->addTextArea('text', 'Label')
		->setValue('&text')
		->setHtmlAttribute('autocomplete', 'off');

	Assert::type(Html::class, $input->getLabel());
	Assert::same('<label for="frm-text">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type(Html::class, $input->getControl());
	Assert::same('<textarea name="text" autocomplete="off" id="frm-text">&amp;text</textarea>', (string) $input->getControl());
});


test('placeholder translation', function () {
	$form = new Form;
	$input = $form->addTextArea('text', 'Label')
		->setHtmlAttribute('placeholder', 'place')
		->setValue('text')
		->setTranslator(new Translator);

	Assert::same('<label for="frm-text">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text">Another label</label>', (string) $input->getLabel('Another label'));
	Assert::same('<textarea name="text" placeholder="PLACE" id="frm-text">text</textarea>', (string) $input->getControl());
});


test('HTML label translation', function () {
	$form = new Form;
	$input = $form->addTextArea('text', Html::el('b', 'Label'))
		->setTranslator(new Translator);

	Assert::same('<label for="frm-text"><b>Label</b></label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
});


test('length rule attributes', function () {
	$form = new Form;
	$input = $form->addTextArea('text')
		->addRule($form::Length, null, [10, 20]);

	Assert::same('<textarea name="text" maxlength="20" id="frm-text" data-nette-rules=\'[{"op":":length","msg":"Please enter a value between 10 and 20 characters long.","arg":[10,20]}]\'></textarea>', (string) $input->getControl());
});


test('multiple max length rules', function () {
	$form = new Form;
	$input = $form->addTextArea('text')
		->addRule($form::MaxLength, null, 30)
		->addRule($form::MaxLength, null, 10);

	Assert::same('<textarea name="text" maxlength="10" id="frm-text" data-nette-rules=\'[{"op":":maxLength","msg":"Please enter no more than 30 characters.","arg":30},{"op":":maxLength","msg":"Please enter no more than 10 characters.","arg":10}]\'></textarea>', (string) $input->getControl());
});


test('textarea in container', function () {
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addTextArea('text');

	Assert::same('<textarea name="container[text]" id="frm-container-text"></textarea>', (string) $input->getControl());
});


test('control options handling', function () {
	$form = new Form;
	$input = $form->addTextArea('text');

	Assert::same('textarea', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});


test('empty value handling', function () {
	$form = new Form;
	$input = $form->addTextArea('text')
		->setEmptyValue('empty ');

	Assert::same('<textarea name="text" id="frm-text" data-nette-empty-value="empty">empty </textarea>', (string) $input->getControl());
});


test('nullable empty value', function () {
	$form = new Form;
	$input = $form->addTextArea('text')
		->setEmptyValue('empty ')
		->setNullable();

	Assert::null($input->getValue());
	Assert::same('<textarea name="text" id="frm-text" data-nette-empty-value="empty">empty </textarea>', (string) $input->getControl());
});


test('filter and rule combination', function () {
	$form = new Form;
	$input = $form->addTextArea('text')
		->addFilter(function () {})
		->addRule(Form::MaxLength, 'maxl', 10);

	Assert::same('<textarea name="text" id="frm-text"></textarea>', (string) $input->getControl());
});
