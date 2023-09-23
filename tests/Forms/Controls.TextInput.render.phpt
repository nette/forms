<?php

/**
 * Test: Nette\Forms\Controls\TextInput.
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
	$input = $form->addText('text', 'Label')
		->setValue('text')
		->setHtmlAttribute('autocomplete', 'off');

	Assert::type(Html::class, $input->getLabel());
	Assert::same('<label for="frm-text">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="text" name="text" autocomplete="off" id="frm-text" value="text">', (string) $input->getControl());
});


test('translator', function () {
	$form = new Form;
	$input = $form->addText('text', 'Label')
		->setHtmlAttribute('placeholder', 'place')
		->setValue('text')
		->setTranslator(new Translator)
		->setEmptyValue('xxx');

	Assert::same('<label for="frm-text">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text">Another label</label>', (string) $input->getLabel('Another label'));
	Assert::same('<input type="text" name="text" placeholder="PLACE" id="frm-text" data-nette-empty-value="XXX" value="text">', (string) $input->getControl());
});


test('Html with translator', function () {
	$form = new Form;
	$input = $form->addText('text', Html::el('b', 'Label'))
		->setTranslator(new Translator);

	Assert::same('<label for="frm-text"><b>Label</b></label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
});


test('Html with label placeholder in validation rule message', function () {
	$form = new Form;
	$input = $form->addText('text', Html::el('b', 'Label:'))
		->addRule(Form::Required, 'Please fill in %label');

	Assert::same('<label for="frm-text"><b>Label:</b></label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="text" name="text" id="frm-text" required data-nette-rules=\'[{"op":":filled","msg":"Please fill in Label"}]\'>', (string) $input->getControl());
});


test('password', function () {
	$form = new Form;
	$input = $form->addPassword('password')
		->setValue('xxx');

	Assert::same('<input type="password" name="password" id="frm-password">', (string) $input->getControl());
});


test('validation rule required & PATTERN', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setRequired('required')
		->addRule($form::Pattern, 'error message', '[0-9]+');

	foreach (['text', 'search', 'tel', 'url', 'email'] as $type) {
		$input->setHtmlType($type);
		Assert::same('<input type="' . $type . '" name="text" pattern="[0-9]+" id="frm-text" required data-nette-rules=\'[{"op":":filled","msg":"required"},{"op":":pattern","msg":"error message","arg":"[0-9]+"}]\'>', (string) $input->getControl());
	}

	$input->setHtmlType('password');
	Assert::same('<input type="password" name="text" pattern="[0-9]+" id="frm-text" required data-nette-rules=\'[{"op":":filled","msg":"required"},{"op":":pattern","msg":"error message","arg":"[0-9]+"}]\'>', (string) $input->getControl());

	$input->setHtmlType('number');
	Assert::same('<input type="number" name="text" pattern="[0-9]+" id="frm-text" required data-nette-rules=\'[{"op":":filled","msg":"required"},{"op":":pattern","msg":"error message","arg":"[0-9]+"}]\'>', (string) $input->getControl());
});


test('conditional required', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::Filled)
			->addRule($form::Filled);

	Assert::same('<input type="text" name="text" id="frm-text" data-nette-rules=\'[{"op":":filled","rules":[{"op":":filled","msg":"This field is required."}],"control":"text"}]\'>', (string) $input->getControl());
});


test('maxlength without validation rule', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setMaxLength(30);

	Assert::same('<input type="text" name="text" maxlength="30" id="frm-text">', (string) $input->getControl());
});


test('validation rule LENGTH', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setMaxLength(30)
		->addRule($form::Length, null, [10, 20]);

	Assert::same('<input type="text" name="text" maxlength="20" id="frm-text" data-nette-rules=\'[{"op":":length","msg":"Please enter a value between 10 and 20 characters long.","arg":[10,20]}]\'>', (string) $input->getControl());
});


test('validation rule MAX_LENGTH', function () {
	$form = new Form;
	$input = $form->addText('text', null, null, 30)
		->addRule($form::MaxLength, null, 10);

	Assert::same('<input type="text" name="text" maxlength="10" id="frm-text" data-nette-rules=\'[{"op":":maxLength","msg":"Please enter no more than 10 characters.","arg":10}]\'>', (string) $input->getControl());
});


test('validation rule RANGE without setHtmlType', function () {
	$form = new Form;
	$minInput = $form->addText('min');
	$maxInput = $form->addText('max');
	$input = $form->addText('count')
		->addRule(Form::Range, 'Must be in range from %d to %d', [0, 100])
		->addRule(Form::Min, 'Must be greater than or equal to %d', 1)
		->addRule(Form::Max, 'Must be less than or equal to %d', 101)
		->addRule(Form::Range, 'Must be in range from %d to %d', [$minInput, $maxInput]);

	Assert::same('<input type="text" name="count" id="frm-count" data-nette-rules=\'[{"op":":range","msg":"Must be in range from 0 to 100","arg":[0,100]},{"op":":min","msg":"Must be greater than or equal to 1","arg":1},{"op":":max","msg":"Must be less than or equal to 101","arg":101},{"op":":range","msg":"Must be in range from %0 to %1","arg":[{"control":"min"},{"control":"max"}]}]\'>', (string) $input->getControl());
});


test('validation rule RANGE with setHtmlType', function () {
	$form = new Form;
	$minInput = $form->addText('min');
	$maxInput = $form->addText('max');
	$input = $form->addText('count')
		->setHtmlType('number')
		->addRule(Form::Range, 'Must be in range from %d to %d', [0, 100])
		->addRule(Form::Min, 'Must be greater than or equal to %d', 1)
		->addRule(Form::Max, 'Must be less than or equal to %d', 101)
		->addRule(Form::Range, 'Must be in range from %d to %d', [$minInput, $maxInput]);

	Assert::same('<input type="number" name="count" min="1" max="100" id="frm-count" data-nette-rules=\'[{"op":":range","msg":"Must be in range from 0 to 100","arg":[0,100]},{"op":":min","msg":"Must be greater than or equal to 1","arg":1},{"op":":max","msg":"Must be less than or equal to 101","arg":101},{"op":":range","msg":"Must be in range from %0 to %1","arg":[{"control":"min"},{"control":"max"}]}]\'>', (string) $input->getControl());
});


test('setEmptyValue', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setEmptyValue('empty ');

	Assert::same('<input type="text" name="text" id="frm-text" data-nette-empty-value="empty" value="empty ">', (string) $input->getControl());
});


test('setNullable', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setNullable();

	Assert::null($input->getValue());
});


test('setEmptyValue & setNullable', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setEmptyValue('empty ')
		->setNullable();

	Assert::null($input->getValue());
	Assert::same('<input type="text" name="text" id="frm-text" data-nette-empty-value="empty" value="empty ">', (string) $input->getControl());
});


test('setDefaultValue', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setDefaultValue('default');

	Assert::same('<input type="text" name="text" id="frm-text" value="default">', (string) $input->getControl());
});


test('container', function () {
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addText('text');

	Assert::same('<input type="text" name="container[text]" id="frm-container-text">', (string) $input->getControl());
});


test('rendering options', function () {
	$form = new Form;
	$input = $form->addText('text');

	Assert::same('text', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});


test('addInteger', function () {
	$form = new Form;
	$input = $form->addInteger('text');

	Assert::null($input->getValue());

	Assert::same('<input type="number" name="text" id="frm-text" data-nette-rules=\'[{"op":":integer","msg":"Please enter a valid integer."}]\'>', (string) $input->getControl());
});


test('addFloat', function () {
	$form = new Form;
	$input = $form->addFloat('text');

	Assert::null($input->getValue());

	Assert::same('<input type="number" name="text" step="any" id="frm-text" data-nette-rules=\'[{"op":":float","msg":"Please enter a valid number."}]\'>', (string) $input->getControl());
});


test('addFilter() & rules', function () {
	$form = new Form;
	$input = $form->addText('text')
		->addRule(Form::Min, 'min', 1)
		->addFilter(function () {})
		->addRule(Form::Max, 'max', 10)
		->addRule(Form::MaxLength, 'maxl', 10);

	Assert::same('<input type="text" name="text" id="frm-text" data-nette-rules=\'[{"op":":min","msg":"min","arg":1}]\'>', (string) $input->getControl());
});
