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


test('date/time bounds on a text input render in the value format of its type', function () {
	$form = new Form;
	$input = $form->addText('d')->setHtmlType('date');
	$input->addRule(new Validation\RangeValidator(min: new DateTimeImmutable('2026-01-01 10:30')));
	$control = (string) $input->getControl();
	Assert::contains('min="2026-01-01"', $control);
	Assert::contains('"arg":"2026-01-01"', $control);

	$month = $form->addText('m')->setHtmlType('month')
		->addRule(Form::Range, null, [new DateTimeImmutable('2026-01-15'), new DateTimeImmutable('2026-06-15')]);
	Assert::contains('min="2026-01" max="2026-06"', (string) $month->getControl());

	$local = $form->addText('l')->setHtmlType('datetime-local')
		->addRule(Form::Max, null, new DateTimeImmutable('2026-01-01 10:30'));
	Assert::contains('max="2026-01-01T10:30"', (string) $local->getControl());

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
	$failure = new Validation\MimeTypeValidator('image/*')->validate($file);
	Assert::same('forms.mimeType', $failure->id);
	Assert::same(['type' => $detected, 'file' => 'document.html'], $failure->parameters);
	Assert::same("The uploaded file is in an unsupported format $detected.", $failure->substitute($failure->template));

	Assert::null(new Validation\MimeTypeValidator('text/*')->validate($file));
});


test('length rules on multi-value controls count items and carry stable ids with plurals', function () {
	// value-based validator is usable without a form
	$failure = new Validation\ItemCountValidator(min: 2)->validate(['a']);
	Assert::same('forms.count.min', $failure->id);
	Assert::same(2, $failure->count);
	Assert::same('Please select at least %count items.', (string) $failure);

	// the string length validator does not count items
	Assert::same('forms.length.min', new Validation\LengthValidator(min: 2)->validate(['a', 'b'])?->id);

	$form = new Form;
	$list = $form->addCheckboxList('tags', items: ['a' => 'A', 'b' => 'B', 'c' => 'C']);
	$list->setValue(['a']);
	$list->addRule(Form::MinLength, null, 2);
	$list->validate();
	Assert::same(['Please select at least 2 items.'], array_map(strval(...), $list->getErrors()));
	Assert::same(
		[['op' => ':minLength', 'msg' => 'Please select at least 2 items.', 'arg' => 2]],
		Nette\Forms\ClientRuleExporter::export($list->getRules()),
	);

	$select = $form->addMultiSelect('cities', items: ['p' => 'Praha', 'b' => 'Brno', 'o' => 'Ostrava']);
	$select->setValue(['p', 'b', 'o']);
	$select->addRule(Form::Count, null, [null, 2]);
	$select->validate();
	Assert::same(['Please select no more than 2 items.'], array_map(strval(...), $select->getErrors()));

	$exact = $form->addCheckboxList('pair', items: ['a' => 'A', 'b' => 'B', 'c' => 'C']);
	$exact->setValue(['a']);
	$exact->addRule(Form::Length, null, 2);
	$exact->validate();
	Assert::same(['Please select exactly 2 items.'], array_map(strval(...), $exact->getErrors()));

	// text controls keep character semantics
	$text = $form->addText('note')->setValue('x');
	$text->addRule(Form::MinLength, null, 2);
	$text->validate();
	Assert::same(['Please enter at least 2 characters.'], array_map(strval(...), $text->getErrors()));
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


test('the content of array and class arguments is verified at declaration', function () {
	$form = new Form;
	$input = $form->addText('name');

	Assert::exception(
		fn() => $input->addRule(Form::Range, null, [1, [2]]),
		Nette\InvalidArgumentException::class,
		"Validation rule :range for control 'name' expects array of two bounds as the argument, array given.",
	);
	Assert::exception(
		fn() => $input->addRule(Form::Range, null, [null, null]),
		Nette\InvalidArgumentException::class,
		"Validation rule :range for control 'name' expects array of two bounds as the argument, array given.",
	);
	Assert::exception(
		fn() => $input->addCondition(Form::Enum, 'NonExistentEnum'),
		Nette\InvalidArgumentException::class,
		"Validation rule :enum for control 'name' expects class name of a backed enum as the argument, string given.",
	);
	Assert::exception(
		fn() => $form->addUpload('file')->addRule(Form::MimeType, null, ['image/png', 5]),
		Nette\InvalidArgumentException::class,
		"Validation rule :mimeType for control 'file' expects string or array of strings as the argument, array given.",
	);

	$input->addRule(Form::Range, null, ['a', null]);
	$input->addRule(Form::Range, null, [null, $form->addText('max')]);
});


test('control-valued bounds of length and pattern resolve at validation time', function () {
	$form = new Form;
	$limit = $form->addInteger('limit')->setValue(3);
	$source = $form->addText('source')->setValue('a+');
	$input = $form->addText('name')->setValue('aaaa');
	$input->addRule(Form::MaxLength, null, $limit);
	$input->validate();
	Assert::same(['Please enter no more than 3 characters.'], $input->getErrors());

	$other = $form->addText('other')->setValue('ab');
	$other->addRule(Form::Pattern, null, $source);
	$other->validate();
	Assert::same(['Please enter a value in the required format.'], $other->getErrors());

	Assert::same(
		[['op' => ':maxLength', 'msg' => 'Please enter no more than %0 characters.', 'arg' => ['control' => 'limit']]],
		Nette\Forms\ClientRuleExporter::export($input->getRules()),
	);
});


test('NotEqual and Numeric operations have validator objects', function () {
	$form = new Form;
	$name = $form->addText('name')->setValue('admin');
	$name->addRule(Form::NotEqual, null, ['root', 'admin']);
	$name->validate();
	Assert::same(['This value should not be root, admin.'], $name->getErrors());
	Assert::same(
		[['op' => ':notEqual', 'msg' => 'This value should not be root, admin.', 'arg' => ['root', 'admin']]],
		Nette\Forms\ClientRuleExporter::export($name->getRules()),
	);

	Assert::null(new Validation\NotEqualValidator('x')->validate('y'));
	Assert::same('forms.numeric', new Validation\NumericValidator()->validate('-1')?->id);
	Assert::null(new Validation\NumericValidator()->validate('42'));
});


test('a validator object must say whether it inspects the value or the control', function () {
	$bare = new class implements Validation\Validator {
		public function getClientRule(): ?Validation\ClientRule
		{
			return null;
		}
	};

	$form = new Form;
	$input = $form->addText('name');
	Assert::exception(
		fn() => $input->addRule($bare),
		Nette\InvalidArgumentException::class,
		'Validator %a% must implement Nette\Forms\Validation\ValueValidator, Nette\Forms\Validation\ControlValidator or Nette\Forms\Validation\Filter.',
	);
	Assert::exception(
		fn() => $input->addCondition($bare),
		Nette\InvalidArgumentException::class,
		'Validator %a% must implement %a%.',
	);
	Assert::exception(
		fn() => $input->addConditionOn($form->addText('other'), $bare),
		Nette\InvalidArgumentException::class,
		'Validator %a% must implement %a%.',
	);
});


test('a custom control validator inspects the control', function () {
	$disabled = new class implements Validation\ControlValidator {
		public function validateControl(Nette\Forms\Control $control): ?Validation\Message
		{
			return $control->isDisabled() ? null : new Validation\Message('app.disabled', 'Control %label must be disabled.');
		}


		public function getClientRule(): ?Validation\ClientRule
		{
			return null;
		}
	};

	$form = new Form;
	$input = $form->addText('name', 'Name')->setValue('x')->addRule($disabled);
	$input->validate();
	Assert::same(['Control Name must be disabled.'], $input->getErrors());

	$toggled = $form->addText('other')->setValue('x');
	$toggled->addCondition($disabled)->addRule(Form::Email, 'unreachable');
	$toggled->validate();
	Assert::same([], $toggled->getErrors());
});
