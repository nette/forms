<?php declare(strict_types=1);

/**
 * Test: validator objects in Rules
 */

use Nette\Forms\{Form, Validation};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


enum TestRuleSuit: string
{
	case Hearts = 'h';
	case Spades = 's';
}


test('validator object validates and produces its default message', function () {
	$form = new Form;
	$input = $form->addText('email')->setValue('invalid');
	$input->addRule(new Validation\EmailValidator);
	$input->validate();
	Assert::same(['Please enter a valid email address.'], $input->getErrors());
});


test('empty optional control skips validator objects', function () {
	$form = new Form;
	$input = $form->addText('email')->setValue('');
	$input->addRule(new Validation\EmailValidator);
	$input->validate();
	Assert::same([], $input->getErrors());
});


test('user message overrides the validator default and placeholders work', function () {
	$form = new Form;
	$input = $form->addText('email', 'E-mail:')->setValue('wrong');
	$input->addRule(new Validation\EmailValidator, '%label %value is invalid');
	$input->validate();
	Assert::same(['E-mail wrong is invalid'], $input->getErrors());
});


test('named parameters discovered by the validator are substituted', function () {
	$form = new Form;
	$input = $form->addText('age')->setValue('5');
	$input->addRule(new Validation\RangeValidator(min: 18));
	$input->validate();
	Assert::same(['Please enter a value greater than or equal to 18.'], $input->getErrors());
});


test('message kind depends on the failed bound', function () {
	$form = new Form;
	$input = $form->addText('age')->setValue('7');
	$input->addRule(new Validation\RangeValidator(min: 1, max: 5));
	$input->validate();
	Assert::same(['Please enter a value between 1 and 5.'], $input->getErrors());
});


test('length validator distinguishes min, max and exact', function () {
	$form = new Form;
	$short = $form->addText('a')->setValue('ab');
	$short->addRule(new Validation\LengthValidator(min: 3));
	$short->validate();
	Assert::same(['Please enter at least 3 characters.'], $short->getErrors());

	$long = $form->addText('b')->setValue('abcdef');
	$long->addRule(new Validation\LengthValidator(max: 5));
	$long->validate();
	Assert::same(['Please enter no more than 5 characters.'], $long->getErrors());

	$exact = $form->addText('c')->setValue('ab');
	$exact->addRule(new Validation\LengthValidator(3, 3));
	$exact->validate();
	Assert::same(['Please enter exactly 3 characters.'], $exact->getErrors());
});


test('filled validator object occupies the required slot', function () {
	$form = new Form;
	$input = $form->addText('name');
	$input->addRule(new Validation\FilledValidator);
	Assert::true($input->isRequired());

	$input->validate();
	Assert::same(['This field is required.'], $input->getErrors());

	$input->setRequired(false);
	Assert::false($input->isRequired());
});


test('condition with a validator object', function () {
	$form = new Form;
	$input = $form->addText('email');
	$input->addCondition(new Validation\LengthValidator(min: 1))
		->addRule(Form::Email);

	$input->setValue('');
	$input->validate();
	Assert::same([], $input->getErrors());

	$input->setValue('not-an-email');
	$input->validate();
	Assert::same(['Please enter a valid email address.'], $input->getErrors());
});


test('removeRule() accepts the validator class name', function () {
	$form = new Form;
	$input = $form->addText('email')->setValue('wrong');
	$input->addRule(new Validation\EmailValidator);
	$input->getRules()->removeRule(Validation\EmailValidator::class);
	$input->validate();
	Assert::same([], $input->getErrors());

	$input->addRule(new Validation\FilledValidator);
	Assert::true($input->isRequired());
	$input->getRules()->removeRule(Validation\FilledValidator::class);
	Assert::false($input->isRequired());
});


testException('validator objects reject the $arg argument', function () {
	$form = new Form;
	$form->addText('email')->addRule(new Validation\EmailValidator, null, 'arg');
}, Nette\InvalidArgumentException::class, 'Validator objects carry their configuration themselves, the $arg argument must be null.');


test('integer validator normalizes the value after successful validation', function () {
	$form = new Form;
	$input = $form->addText('num')->setValue('42');
	$input->addRule(new Validation\IntegerValidator);
	$input->validate();
	Assert::same([], $input->getErrors());
	Assert::same(42, $input->getValue());

	$invalid = $form->addText('bad')->setValue('4x');
	$invalid->addRule(new Validation\IntegerValidator);
	$invalid->validate();
	Assert::same(['Please enter a valid integer.'], $invalid->getErrors());
	Assert::same('4x', $invalid->getValue());
});


test('equal validator unwraps backed enums in messages and export', function () {
	$form = new Form;
	$input = $form->addText('x')->setValue('q');
	$input->addRule(Form::Equal, null, TestRuleSuit::Hearts);
	$input->validate();
	Assert::same(['Please enter h.'], array_map(strval(...), $input->getErrors()));
	Assert::same(
		[['op' => ':equal', 'msg' => 'Please enter h.', 'arg' => [TestRuleSuit::Hearts]]],
		Nette\Forms\ClientRuleExporter::export($input->getRules()),
	);

	$legacy = $form->addText('y')->setValue('q');
	$legacy->addRule(Form::Equal, 'Enter %s please', TestRuleSuit::Spades);
	$legacy->validate();
	Assert::same(['Enter s please'], array_map(strval(...), $legacy->getErrors()));
});


test('equal validator against another control', function () {
	$form = new Form;
	$form->addText('password')->setValue('secret');
	$verify = $form->addText('verify')->setValue('different');
	$verify->addRule(new Validation\EqualValidator($form['password']));
	$verify->validate();
	Assert::same(['Please enter secret.'], $verify->getErrors());
});


test('date/time bounds on a control without own formatting export to client rules, not to attributes', function () {
	$form = new Form;
	$input = $form->addText('d')->setHtmlType('date');
	$input->addRule(new Validation\RangeValidator(min: new DateTimeImmutable('2026-01-01')));
	$control = (string) $input->getControl();
	Assert::notContains('min=', $control);
	Assert::contains('"arg":"2026-01-01T00:00:00"', $control);

	$legacy = $form->addText('x');
	$legacy->addRule(Form::Min, 'after %d', new DateTimeImmutable('2026-01-01 10:30'));
	Assert::same(
		[['op' => ':min', 'msg' => 'after 2026-01-01 10:30:00', 'arg' => '2026-01-01T10:30:00']],
		Nette\Forms\ClientRuleExporter::export($legacy->getRules()),
	);
});


test('range validator coerces string and timestamp bounds for date/time values', function () {
	$form = new Form;
	$date = $form->addDate('when');
	$date->setValue('2026-07-01');
	$date->addRule(new Validation\RangeValidator(min: '2026-07-10'));
	$date->validate();
	Assert::match('Please enter a value greater than or equal to %a%.', (string) $date->getErrors()[0]);

	$ok = $form->addDate('ok');
	$ok->setValue('2026-07-15');
	$ok->addRule(new Validation\RangeValidator(min: '2026-07-10', max: '2026-07-20'));
	$ok->validate();
	Assert::same([], $ok->getErrors());
});


test('else branch of an object condition behaves like the string one', function () {
	$errors = [];
	foreach ([new Validation\FilledValidator, Form::Filled] as $condition) {
		$form = new Form;
		$input = $form->addText('x')->setValue('');
		$input->addCondition($condition)
			->addRule(Form::Email)
			->elseCondition()
				->addRule(Form::MinLength, 'else branch fired: %d', 5)
			->endCondition();
		$input->validate();
		$errors[] = array_map(strval(...), $input->getErrors());
	}

	Assert::same([['else branch fired: 5'], ['else branch fired: 5']], $errors);
});


test('validator objects set the input type like their legacy counterparts', function () {
	$form = new Form;
	$email = $form->addText('mail')->addRule(new Validation\EmailValidator);
	Assert::contains('type="email"', (string) $email->getControl());

	$number = $form->addText('num')->addRule(new Validation\IntegerValidator);
	Assert::contains('type="number"', (string) $number->getControl());
});


test('file size validator object triggers the ini limit warning and replaces the default rule', function () {
	$form = new Form;
	$upload = $form->addUpload('file');
	$huge = Nette\Forms\Helpers::iniGetSize('upload_max_filesize') + 1;
	Assert::error(
		fn() => $upload->addRule(new Validation\FileSizeValidator($huge)),
		E_USER_WARNING,
		"Value of MaxFileSize ($huge) is greater than value of directive upload_max_filesize (%a%).",
	);
	$exported = Nette\Forms\ClientRuleExporter::export($upload->getRules());
	$fileSizeRules = array_filter($exported, fn($item) => $item['op'] === ':fileSize');
	Assert::count(1, $fileSizeRules);
});


test('mime type validator passes the detected type to the message (issue #302)', function () {
	$file = new Nette\Http\FileUpload([
		'name' => 'document.html',
		'size' => 100,
		'tmp_name' => __FILE__, // detected as PHP source, certainly not an image
		'error' => UPLOAD_ERR_OK,
	]);
	$detected = strtolower($file->getContentType());

	// value-based validators are usable and testable without any form
	$failure = (new Validation\MimeTypeValidator('image/*'))->validate($file);
	Assert::same('forms.mimeType', $failure->id);
	Assert::same(['type' => $detected, 'file' => 'document.html'], $failure->parameters);
	Assert::same("The uploaded file is in an unsupported format $detected.", (string) $failure);

	Assert::null((new Validation\MimeTypeValidator('text/*'))->validate($file));
});


test('mistyped operation argument fails at the place of declaration, not at submit time', function () {
	$form = new Form;
	$input = $form->addText('name');

	Assert::exception(
		fn() => $input->addRule(Form::MinLength, null, '3'),
		Nette\InvalidArgumentException::class,
		"Validation rule :minLength for control 'name' expects int as the argument, string given.",
	);

	Assert::exception(
		fn() => $input->addRule(Form::Pattern, null, 123),
		Nette\InvalidArgumentException::class,
		"Validation rule :pattern for control 'name' expects string pattern as the argument, int given.",
	);

	Assert::exception(
		fn() => $input->addRule(Form::Min),
		Nette\InvalidArgumentException::class,
		"Validation rule :min for control 'name' expects int, float, string, date/time or Control as the argument, null given.",
	);

	Assert::exception(
		fn() => $form->addUpload('file')->addRule(Form::MaxFileSize, null, '1M'),
		Nette\InvalidArgumentException::class,
		"Validation rule :fileSize for control 'file' expects int (bytes) as the argument, string given.",
	);

	Assert::exception(
		fn() => $input->addCondition(Form::Length, '5'),
		Nette\InvalidArgumentException::class,
		"Validation rule :length for control 'name' expects int or array of two int bounds as the argument, string given.",
	);

	// a mixed bounds array gets the friendly exception too, not a TypeError from the validator constructor
	Assert::exception(
		fn() => $form->addCheckboxList('tags', items: ['a' => 'A'])->addRule(Form::Count, null, ['2', 5]),
		Nette\InvalidArgumentException::class,
		"Validation rule :length for control 'tags' expects int or array of two int bounds as the argument, array given.",
	);

	// valid declarations are unaffected
	$input->addRule(Form::MinLength, null, 3);
	$input->addRule(Form::MaxLength, null, $form->addInteger('limit'));
	$input->addCondition(Form::Length, [1, 5]);
	// Control-valued arguments resolve at validation time and stay declarable
	$input->addRule(Form::Pattern, null, $form->addText('patternSource'));
	$input->addRule(Form::Length, null, [$form['limit'], 5]);
});
