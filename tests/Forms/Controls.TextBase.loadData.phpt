<?php

/**
 * Test: Nette\Forms\Controls\TextInput.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';
	Form::initialize(true);
});


test('trim & new lines', function () {
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$input = $form->addText('text');

	Assert::same('a  b   c', $input->getValue());
	Assert::true($input->isFilled());
});


test('trim & new lines in textarea', function () {
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$input = $form->addTextArea('text');

	Assert::same("  a\n b \n c ", $input->getValue());
});


test('empty value', function () {
	$_POST = ['url' => 'nette.org'];

	$form = new Form;
	$input = $form->addText('url')
		->setEmptyValue('nette.org');

	Assert::same('', $input->getValue());
});


test('empty value', function () {
	$_POST = ['phone' => '+420 '];

	$form = new Form;
	$input = $form->addText('phone')
		->setEmptyValue('+420 ');

	Assert::same('', $input->getValue());
});


test('invalid UTF', function () {
	$_POST = ['invalidutf' => "invalid\xAA\xAA\xAAutf"];

	$form = new Form;
	$input = $form->addText('invalidutf');
	Assert::same('', $input->getValue());
});


test('missing data', function () {
	$form = new Form;
	$input = $form->addText('unknown');

	Assert::same('', $input->getValue());
	Assert::false($input->isFilled());
});


test('malformed data', function () {
	$_POST = ['malformed' => ['']];

	$form = new Form;
	$input = $form->addText('malformed');

	Assert::same('', $input->getValue());
	Assert::false($input->isFilled());
});


test('setValue() and invalid argument', function () {
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$input = $form->addText('text');
	$input->setValue(null);

	Assert::exception(function () use ($input) {
		$input->setValue([]);
	}, Nette\InvalidArgumentException::class, "Value must be scalar or null, array given in field 'text'.");
});


test('float', function () {
	$_POST = ['number' => ' 10,5 '];

	$form = new Form;
	$input = $form->addText('number')
		->addRule($form::Float);

	Assert::same('10,5', $input->getValue());
	$input->validate();
	Assert::same(10.5, $input->getValue());
});



test('float in condition', function () {
	$_POST = ['number' => ' 10,5 '];

	$form = new Form;
	$input = $form->addText('number');
	$input->addCondition($form::Filled)
			->addRule($form::Float);

	$input->validate();
	Assert::same(10.5, $input->getValue());
});


test('non float', function () {
	$_POST = ['number' => ' 10,5 '];

	$form = new Form;
	$input = @$form->addText('number')
		->addRule(~$form::Float); // @ - negative rules are deprecated

	$input->validate();
	Assert::same(10.5, $input->getValue()); // side effect
});


test('URL', function () {
	$_POST = ['url' => 'nette.org'];

	$form = new Form;
	$input = $form->addText('url')
		->addRule($form::URL);

	$input->validate();
	Assert::same('https://nette.org', $input->getValue());
});


test('object', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setValue($date = new Nette\Utils\DateTime('2013-07-05'));

	Assert::same($date, $input->getValue());
});


test('filter', function () {
	$_POST = ['text' => 'hello'];

	$form = new Form;
	$input = $form->addText('text')
		->addFilter('strrev');

	Assert::same('hello', $input->getValue());
	$input->validate();
	Assert::same('olleh', $input->getValue());
});


test('filter in condition', function () {
	$_POST = ['text' => 'hello'];

	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::Filled)
			->addFilter('strrev');

	Assert::same('hello', $input->getValue());
	$input->validate();
	Assert::same('olleh', $input->getValue());
});


test('filter in BLANK condition', function () {
	$_POST = ['text' => ''];

	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::Blank)
		->addFilter(function () use ($input) {
			return 'default';
		});

	Assert::same('', $input->getValue());
	$input->validate();
	Assert::same('default', $input->getValue());
});


test('filter in !FILLED condition', function () {
	$_POST = ['text' => ''];

	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::Filled)
		->elseCondition()
		->addFilter(function () use ($input) {
			return 'default';
		});

	Assert::same('', $input->getValue());
	$input->validate();
	Assert::same('default', $input->getValue());
});
