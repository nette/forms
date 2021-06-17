<?php

/**
 * Test: Nette\Forms\Controls\Button & SubmitButton
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
	$input = $form->addButton('button', 'Caption');

	Assert::null($input->getLabel());
	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="button" name="button" value="Caption">', (string) $input->getControl());
	Assert::same('<input type="button" name="button" value="Another caption">', (string) $input->getControl('Another caption'));
});


test('<button>', function () {
	$form = new Form;
	$input = $form->addButton('button', 'Caption');
	$input->renderAsButton();

	Assert::same('<button type="button" name="button">Caption</button>', (string) $input->getControl());
	Assert::same('<button type="button" name="button">Another label</button>', (string) $input->getControl('Another label'));
});


test('Html', function () {
	$form = new Form;
	$input = $form->addButton('button', Html::el('b', 'Caption'));

	Assert::same('<button type="button" name="button"><b>Caption</b></button>', (string) $input->getControl());
	Assert::same('<input type="button" name="button" value="Another caption">', (string) $input->getControl('Another caption'));
	Assert::same('<button type="button" name="button"><b>Another label</b></button>', (string) $input->getControl(Html::el('b', 'Another label')));
});


test('translator', function () {
	$form = new Form;
	$input = $form->addButton('button', 'Caption');
	$input->setTranslator(new Translator);

	Assert::same('<input type="button" name="button" value="CAPTION">', (string) $input->getControl());
	Assert::same('<input type="button" name="button" value="ANOTHER CAPTION">', (string) $input->getControl('Another caption'));
});


test('Html with translator', function () {
	$form = new Form;
	$input = $form->addButton('button', Html::el('b', 'Caption'));
	$input->setTranslator(new Translator);

	Assert::same('<button type="button" name="button"><b>Caption</b></button>', (string) $input->getControl());
	Assert::same('<button type="button" name="button"><b>Another label</b></button>', (string) $input->getControl(Html::el('b', 'Another label')));
});


test('no validation rules', function () {
	$form = new Form;
	$input = $form->addButton('button', 'Caption')->setRequired('required');

	Assert::same('<input type="button" name="button" value="Caption">', (string) $input->getControl());
});


test('container', function () {
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addButton('button', 'Caption');

	Assert::same('<input type="button" name="container[button]" value="Caption">', (string) $input->getControl());
});


test('SubmitButton', function () {
	$form = new Form;
	$input = $form->addSubmit('button', 'Caption');

	Assert::null($input->getLabel());
	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="submit" name="button" value="Caption">', (string) $input->getControl());
	Assert::same('<input type="submit" name="button" value="Another caption">', (string) $input->getControl('Another caption'));
});


test('SubmitButton with scope', function () {
	$form = new Form;
	$input = $form->addSubmit('button', 'Caption')->setValidationScope([]);

	Assert::same('<input type="submit" name="button" value="Caption" formnovalidate>', (string) $input->getControl());
});


test('SubmitButton with scope', function () {
	$form = new Form;
	$text = $form->addText('text');
	$select = $form->addSelect('select');
	$input = $form->addSubmit('button', 'Caption')->setValidationScope([$text, $select]);

	Assert::same('<input type="submit" name="button" value="Caption" formnovalidate data-nette-validation-scope=\'["text","select"]\'>', (string) $input->getControl());
});


test('forced ID', function () {
	$form = new Form;
	$input = $form->addButton('button', 'Caption');
	$input->setHtmlId($input->getHtmlId());

	Assert::same('<input type="button" name="button" id="frm-button" value="Caption">', (string) $input->getControl());
});


test('rendering options', function () {
	$form = new Form;
	$input = $form->addButton('button');

	Assert::same('button', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});
