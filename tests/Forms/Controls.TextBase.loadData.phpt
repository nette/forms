<?php

/**
 * Test: Nette\Forms\Controls\TextInput.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
	ob_start();
	Form::initialize(true);
});


test('whitespace trimming', function () {
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$input = $form->addText('text');

	Assert::same('a  b   c', $input->getValue());
	Assert::true($input->isFilled());
});


test('textarea line breaks', function () {
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$input = $form->addTextArea('text');

	Assert::same("  a\n b \n c ", $input->getValue());
});


test('empty value detection', function () {
	$_POST = ['url' => 'nette.org'];

	$form = new Form;
	$input = $form->addText('url')
		->setEmptyValue('nette.org');

	Assert::same('', $input->getValue());
});


test('custom empty value', function () {
	$_POST = ['phone' => '+420 '];

	$form = new Form;
	$input = $form->addText('phone')
		->setEmptyValue('+420 ');

	Assert::same('', $input->getValue());
});


test('invalid UTF input', function () {
	$_POST = ['invalidutf' => "invalid\xAA\xAA\xAAutf"];

	$form = new Form;
	$input = $form->addText('invalidutf');
	Assert::same('', $input->getValue());
});


test('missing POST handling', function () {
	$form = new Form;
	$input = $form->addText('unknown');

	Assert::same('', $input->getValue());
	Assert::false($input->isFilled());
});


test('malformed POST data', function () {
	$_POST = ['malformed' => ['']];

	$form = new Form;
	$input = $form->addText('malformed');

	Assert::same('', $input->getValue());
	Assert::false($input->isFilled());
});


testException('invalid value type exception', function () {
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$input = $form->addText('text');
	$input->setValue([]);
}, Nette\InvalidArgumentException::class, "Value must be scalar or null, array given in field 'text'.");


test('float rule processing', function () {
	$_POST = ['number' => ' 10,5 '];

	$form = new Form;
	$input = $form->addText('number')
		->addRule($form::Float);

	Assert::same('10,5', $input->getValue());
	$input->validate();
	Assert::same(10.5, $input->getValue());
});



test('conditional validation', function () {
	$_POST = ['number' => ' 10,5 '];

	$form = new Form;
	$input = $form->addText('number');
	$input->addCondition($form::Filled)
			->addRule($form::Float);

	$input->validate();
	Assert::same(10.5, $input->getValue());
});


test('negative rule handling', function () {
	$_POST = ['number' => ' 10,5 '];

	$form = new Form;
	$input = @$form->addText('number')
		->addRule(~$form::Float); // @ - negative rules are deprecated

	$input->validate();
	Assert::same(10.5, $input->getValue()); // side effect
});


test('URL auto-correction', function () {
	$_POST = ['url' => 'nette.org'];

	$form = new Form;
	$input = $form->addText('url')
		->addRule($form::URL);

	$input->validate();
	Assert::same('https://nette.org', $input->getValue());
});


test('dateTime value handling', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setValue($date = new Nette\Utils\DateTime('2013-07-05'));

	Assert::same($date, $input->getValue());
});


test('post-validation filtering', function () {
	$_POST = ['text' => 'hello'];

	$form = new Form;
	$input = $form->addText('text')
		->addFilter('strrev');

	Assert::same('hello', $input->getValue());
	$input->validate();
	Assert::same('olleh', $input->getValue());
});


test('conditional filtering', function () {
	$_POST = ['text' => 'hello'];

	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::Filled)
			->addFilter('strrev');

	Assert::same('hello', $input->getValue());
	$input->validate();
	Assert::same('olleh', $input->getValue());
});


test('blank condition filter', function () {
	$_POST = ['text' => ''];

	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::Blank)
		->addFilter(fn() => 'default');

	Assert::same('', $input->getValue());
	$input->validate();
	Assert::same('default', $input->getValue());
});


test('else condition filter', function () {
	$_POST = ['text' => ''];

	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::Filled)
		->elseCondition()
		->addFilter(fn() => 'default');

	Assert::same('', $input->getValue());
	$input->validate();
	Assert::same('default', $input->getValue());
});
