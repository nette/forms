<?php

/**
 * Test: Nette\Forms\Controls\DateTimeControl.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('date', function () {
	$form = new Form;
	$input = $form->addDate('date', 'label');

	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="date" name="date" id="frm-date">', (string) $input->getControl());
});


test('required date', function () {
	$form = new Form;
	$input = $form->addDate('date')->setRequired('required');

	Assert::same('<input type="date" name="date" id="frm-date" required data-nette-rules=\'[{"op":":filled","msg":"required"}]\'>', (string) $input->getControl());
});


test('date: min & max validator', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->addRule($form::Min, null, new DateTime('2020-01-01 11:22:33'))
		->addRule($form::Max, null, new DateTime('2040-01-01 11:22:33'));

	Assert::match('<input type="date" name="date" id="frm-date" data-nette-rules=\'[{"op":":min","msg":"Please enter a value greater than or equal to %a%.","arg":"2020-01-01"},{"op":":max","msg":"Please enter a value less than or equal to %a%.","arg":"2040-01-01"}]\' min="2020-01-01" max="2040-01-01">', (string) $input->getControl());
});


test('date: range validator', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->addRule($form::Range, null, [new DateTime('2020-01-01 11:22:33'), new DateTime('2040-01-01 22:33:44')]);

	Assert::match('<input type="date" name="date" id="frm-date" data-nette-rules=\'[{"op":":range","msg":"Please enter a value between %a% and %a%.","arg":["2020-01-01","2040-01-01"]}]\' min="2020-01-01" max="2040-01-01">', (string) $input->getControl());
});


test('time: range validator', function () {
	$form = new Form;
	$input = $form->addTime('time')
		->addRule($form::Range, null, [new DateTime('2020-01-01 11:22:33'), new DateTime('2040-01-01 22:33:44')]);

	Assert::match('<input type="time" name="time" id="frm-time" data-nette-rules=\'[{"op":":range","msg":"Please enter a value between %a% and %a%.","arg":["11:22","22:33"]}]\' min="11:22" max="22:33">', (string) $input->getControl());
});


test('time with seconds: range validator', function () {
	$form = new Form;
	$input = $form->addTime('time', withSeconds: true)
		->addRule($form::Range, null, [new DateTime('2020-01-01 11:22:33'), new DateTime('2040-01-01 22:33:44')]);

	Assert::match('<input type="time" name="time" step="1" id="frm-time" data-nette-rules=\'[{"op":":range","msg":"Please enter a value between %a% and %a%.","arg":["11:22:33","22:33:44"]}]\' min="11:22:33" max="22:33:44">', (string) $input->getControl());
});


test('date with value', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue(new Nette\Utils\DateTime('2023-10-22'));

	Assert::same('<input type="date" name="date" id="frm-date" value="2023-10-22">', (string) $input->getControl());
});


test('time with value', function () {
	$form = new Form;
	$input = $form->addTime('time')
		->setValue(new Nette\Utils\DateTime('07:05'));

	Assert::same('<input type="time" name="time" id="frm-time" value="07:05">', (string) $input->getControl());
});


test('date-time with value', function () {
	$form = new Form;
	$input = $form->addDateTime('date')
		->setValue(new Nette\Utils\DateTime('2023-10-22 07:05'));

	Assert::same('<input type="datetime-local" name="date" id="frm-date" value="2023-10-22T07:05">', (string) $input->getControl());
});


test('dynamic validation', function () {
	$form = new Form;
	$text = $form->addText('text');
	$input = $form->addDateTime('date')
		->addRule($form::Min, null, $text);

	Assert::same('<input type="datetime-local" name="date" id="frm-date" data-nette-rules=\'[{"op":":min","msg":"Please enter a value greater than or equal to %0.","arg":{"control":"text"}}]\'>', (string) $input->getControl());
});


test('filter in rules', function () {
	$form = new Form;
	$input = $form->addDateTime('date');
	$input->getRules()
		->addFilter(function () {})
		->addRule($form::Min, null, new DateTime('2020-01-01 11:22:33'));

	Assert::same('<input type="datetime-local" name="date" id="frm-date">', (string) $input->getControl());
});
