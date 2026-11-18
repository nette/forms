<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Controls\TextInput.
 */

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
});


test('whitespace trimming', function () {
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('text');

	Assert::same('a  b   c', $input->getValue());
	Assert::true($input->isFilled());
});


test('textarea line breaks', function () {
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addTextArea('text');

	Assert::same("  a\n b \n c ", $input->getValue());
});


test('empty value detection', function () {
	$_POST = ['url' => 'nette.org'];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('url')
		->setEmptyValue('nette.org');

	Assert::same('', $input->getValue());
});


test('custom empty value', function () {
	$_POST = ['phone' => '+420 '];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('phone')
		->setEmptyValue('+420 ');

	Assert::same('', $input->getValue());
});


test('invalid UTF input', function () {
	$_POST = ['invalidutf' => "invalid\xAA\xAA\xAAutf"];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('invalidutf');
	Assert::same('', $input->getValue());
});


test('missing POST handling', function () {
	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('unknown');

	Assert::same('', $input->getValue());
	Assert::false($input->isFilled());
});


test('malformed POST data', function () {
	$_POST = ['malformed' => ['']];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('malformed');

	Assert::same('', $input->getValue());
	Assert::false($input->isFilled());
});


testException('invalid value type exception', function () {
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('text');
	$input->setValue([]);
}, Nette\InvalidArgumentException::class, "Value must be scalar or null, array given in field 'text'.");


test('float rule processing', function () {
	$_POST = ['number' => ' 10,5 '];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('number')
		->addRule($form::Float);

	Assert::same('10,5', $input->getValue());
	$input->validate();
	Assert::same(10.5, $input->getValue());
});


test('addFloat() renders a text input accepting the decimal comma the server accepts', function () {
	$_POST = ['number' => '10,5'];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addFloat('number');

	Assert::contains('type="text"', (string) $input->getControl());
	Assert::contains('inputmode="decimal"', (string) $input->getControl());
	$input->validate();
	Assert::same([], $input->getErrors());
	Assert::same(10.5, $input->getValue());
});


test('conditional validation', function () {
	$_POST = ['number' => ' 10,5 '];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('number');
	$input->addCondition($form::Filled)
		->addRule($form::Float);

	$input->validate();
	Assert::same(10.5, $input->getValue());
});


test('URL auto-correction', function () {
	$_POST = ['url' => 'nette.org'];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('url')
		->addRule($form::URL);

	$input->validate();
	Assert::same('https://nette.org', $input->getValue());
});


test('maxlength attribute is enforced on the submitted text', function () {
	$_POST = ['text' => 'abcd', 'area' => "ab\r\ncd"];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('text', maxLength: 3);
	$input->validate();
	Assert::same(['Please enter no more than 3 characters.'], $input->getErrors());

	$area = $form->addTextArea('area')->setMaxLength(5);
	$area->validate();
	Assert::same([], $area->getErrors()); // line breaks are normalized to \n

	// a failing explicit rule keeps its own message
	$input->addRule($form::MaxLength, 'Custom message', 2);
	$input->validate();
	Assert::same(['Custom message'], $input->getErrors());

	$input->setMaxLength(null);
	$input->getRules()->reset();
	$input->validate();
	Assert::same([], $input->getErrors());
});


test('maxlength attribute measures what the browser sent, not what the application or filters set', function () {
	$_POST = ['web' => 'example.com/abcdef', 'text' => 'abc'];

	$form = new Form;
	$form->allowCrossOrigin();
	$web = $form->addUrl('web', maxLength: 20);
	$web->validate();
	Assert::same([], $web->getErrors());
	Assert::same('https://example.com/abcdef', $web->getValue());
	Assert::contains('value="https://example.com/abcdef"', (string) $web->getControl()); // a re-rendered form shows the filtered value

	$web->validate(); // the filter has already been applied
	Assert::same([], $web->getErrors());

	$text = $form->addText('text', maxLength: 3)->addFilter(fn($s) => "[$s]");
	$text->validate();
	Assert::same([], $text->getErrors());

	$text->setValue('abcdef');
	$text->validate();
	Assert::same([], $text->getErrors());
});


test('maxlength attribute is not checked when nothing was submitted', function () {
	unset($_SERVER['REQUEST_METHOD']);

	$form = new Form;
	$input = $form->addText('text', maxLength: 3)->setDefaultValue('abcd');
	$input->validate();
	Assert::same([], $input->getErrors());
});


test('maxlength attribute counts characters, the browser counts UTF-16 code units', function () {
	// the browser lets in at most one emoji for maxlength=3, as it takes two code units,
	// so the server is the more lenient side
	$_POST = ['ok' => '😀😀😀', 'long' => '😀😀😀😀'];

	$form = new Form;
	$form->allowCrossOrigin();
	$ok = $form->addText('ok', maxLength: 3);
	$long = $form->addText('long', maxLength: 3);
	$ok->validate();
	$long->validate();
	Assert::same([], $ok->getErrors());
	Assert::same(['Please enter no more than 3 characters.'], $long->getErrors());
});


test('maxlength attribute message honours the overrides of Validator::$messages', function () {
	$_POST = ['text' => 'abcd'];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('text', maxLength: 3);
	$original = Nette\Forms\Validator::$messages;
	try {
		Nette\Forms\Validator::$messages[$form::MaxLength] = 'Max %d znaků.';
		$input->validate();
		Assert::same(['Max 3 znaků.'], $input->getErrors());

		Nette\Forms\Validator::$messages[Nette\Forms\Validation\LengthValidator::TooLong] = 'At most %count characters.';
		$input->validate();
		Assert::same(['At most 3 characters.'], $input->getErrors());

		$input->setCaption('Text:');
		Nette\Forms\Validator::$messages[Nette\Forms\Validation\LengthValidator::TooLong] = '%label is longer than %count characters: %value';
		$input->validate();
		Assert::same(['Text is longer than 3 characters: abcd'], $input->getErrors());
	} finally {
		Nette\Forms\Validator::$messages = $original;
	}
});


test('dateTime value handling', function () {
	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('text')
		->setValue($date = new Nette\Utils\DateTime('2013-07-05'));

	Assert::same($date, $input->getValue());
});


test('post-validation filtering', function () {
	$_POST = ['text' => 'hello'];

	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addText('text')
		->addFilter('strrev');

	Assert::same('hello', $input->getValue());
	$input->validate();
	Assert::same('olleh', $input->getValue());
});


test('conditional filtering', function () {
	$_POST = ['text' => 'hello'];

	$form = new Form;
	$form->allowCrossOrigin();
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
	$form->allowCrossOrigin();
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
	$form->allowCrossOrigin();
	$input = $form->addText('text');
	$input->addCondition($form::Filled)
		->elseCondition()
		->addFilter(fn() => 'default');

	Assert::same('', $input->getValue());
	$input->validate();
	Assert::same('default', $input->getValue());
});
