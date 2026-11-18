<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\ClientRuleExporter::export() with validator objects
 */

use Nette\Forms\{ClientRuleExporter, Form, Helpers, Validation};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


enum TestSuit: string
{
	case Hearts = 'h';
	case Spades = 's';
}


test('length validator with both bounds exports one client rule and sets maxlength', function () {
	$form = new Form;
	$input = $form->addText('name');
	$input->addRule(new Validation\LengthValidator(min: 2, max: 5));
	Assert::same([
		['op' => ':length', 'msg' => 'Please enter a value between 2 and 5 characters long.', 'arg' => [2, 5]],
	], ClientRuleExporter::export($input->getRules()));
	Assert::same(5, $input->getControl()->maxlength);
});


test('condition with a validator object yielding a range is exported as one predicate', function () {
	$form = new Form;
	$input = $form->addText('name');
	$input->addCondition(new Validation\LengthValidator(5, 10))->toggle('#x');
	$tags = $form->addCheckboxList('tags', items: ['a' => 'A', 'b' => 'B', 'c' => 'C']);
	$tags->addCondition(new Validation\ItemCountValidator(1, 2))->toggle('#y');

	Assert::same([
		['op' => ':length', 'rules' => [], 'control' => 'name', 'toggle' => ['#x' => true], 'arg' => [5, 10]],
	], ClientRuleExporter::export($input->getRules()));
	Assert::same([
		['op' => ':length', 'rules' => [], 'control' => 'tags[]', 'toggle' => ['#y' => true], 'arg' => [1, 2]],
	], ClientRuleExporter::export($tags->getRules()));
});


test('email validator exports the client rule', function () {
	$form = new Form;
	$input = $form->addText('email');
	$input->addRule(new Validation\EmailValidator);
	Assert::same([
		['op' => ':email', 'msg' => 'Please enter a valid email address.'],
	], ClientRuleExporter::export($input->getRules()));
});


test('enum validator exports as a membership check against the case values', function () {
	$form = new Form;
	$input = $form->addText('suit');
	$input->addRule(new Validation\EnumValidator(TestSuit::class));
	Assert::same([
		['op' => ':equal', 'msg' => 'Please select a valid option.', 'arg' => ['h', 's']],
	], ClientRuleExporter::export($input->getRules()));
});


test('mime type validator sets the accept attribute', function () {
	$form = new Form;
	$upload = $form->addUpload('file');
	$upload->addRule(new Validation\MimeTypeValidator(['image/png', 'image/jpeg']));
	Assert::same('image/png, image/jpeg', $upload->getControl()->accept);

	$limit = Helpers::iniGetSize('upload_max_filesize'); // added by UploadControl itself
	Assert::same([
		['op' => ':fileSize', 'msg' => "The size of the uploaded file can be up to $limit bytes.", 'arg' => $limit],
		['op' => ':mimeType', 'msg' => 'The uploaded file is not in the expected format.', 'arg' => ['image/png', 'image/jpeg']],
	], ClientRuleExporter::export($upload->getRules()));
});


test('control-valued bound is exported as a reference with %N in the message', function () {
	$form = new Form;
	$form->addText('from');
	$input = $form->addText('to');
	$input->addRule(new Validation\RangeValidator(min: $form['from']));
	Assert::same([
		['op' => ':min', 'msg' => 'Please enter a value greater than or equal to %0.', 'arg' => ['control' => 'from']],
	], ClientRuleExporter::export($input->getRules()));
	Assert::null($input->getControl()->min);
});


test('condition with a validator object exports its branch', function () {
	$form = new Form;
	$input = $form->addText('email');
	$input->addCondition(new Validation\FilledValidator)
		->addRule(Form::Email);
	Assert::same([
		[
			'op' => ':filled',
			'rules' => [
				['op' => ':email', 'msg' => 'Please enter a valid email address.'],
			],
			'control' => 'email',
		],
	], ClientRuleExporter::export($input->getRules()));
});


test('user message replaces the message of the exported client rule', function () {
	$form = new Form;
	$input = $form->addText('name');
	$input->addRule(new Validation\LengthValidator(min: 2, max: 5), 'Length must be right');
	Assert::same([
		['op' => ':length', 'msg' => 'Length must be right', 'arg' => [2, 5]],
	], ClientRuleExporter::export($input->getRules()));
});


test('server-only validator object is skipped and the export continues', function () {
	$serverOnly = new class implements Validation\ValueValidator {
		public function validate(mixed $value): ?Validation\Message
		{
			return null;
		}


		public function getClientRule(): ?Validation\ClientRule
		{
			return null;
		}
	};

	$form = new Form;
	$input = $form->addText('name');
	$input->addRule($serverOnly, 'server only');
	$input->addRule(Form::Email);
	Assert::same([
		['op' => ':email', 'msg' => 'Please enter a valid email address.'],
	], ClientRuleExporter::export($input->getRules()));
});


test('range with an open bound on a date control', function () {
	$form = new Form;
	$date = $form->addDate('d');
	$date->addRule(Form::Range, 'too early', ['2026-01-01', null]);
	Assert::same([
		['op' => ':min', 'msg' => 'too early', 'arg' => '2026-01-01'],
	], ClientRuleExporter::export($date->getRules()));
	Assert::contains('min="2026-01-01"', (string) $date->getControl());

	$other = $form->addDate('o');
	$other->addRule(new Validation\EqualValidator(['2026-01-01', null]), 'msg');
	Assert::same([
		['op' => ':equal', 'msg' => 'msg', 'arg' => ['2026-01-01', null]],
	], ClientRuleExporter::export($other->getRules()));

	$date->setValue('2025-12-31');
	$date->validate();
	Assert::count(1, $date->getErrors());
});


test('date/time bound on a text input is exported and shown in the format of the input type', function () {
	$form = new Form;
	$date = $form->addText('date')->setHtmlType('date');
	$date->addRule(Form::Min, null, new DateTimeImmutable('2026-01-01'));
	$month = $form->addText('month')->setHtmlType('month');
	$month->addRule(Form::Max, null, new DateTimeImmutable('2026-03-01'));
	$time = $form->addText('time')->setHtmlType('time');
	$time->addRule(Form::Max, null, new DateTimeImmutable('2000-01-01 10:30'));

	Assert::same([
		['op' => ':min', 'msg' => 'Please enter a value greater than or equal to 2026-01-01.', 'arg' => '2026-01-01'],
	], ClientRuleExporter::export($date->getRules()));
	Assert::same([
		['op' => ':max', 'msg' => 'Please enter a value less than or equal to 2026-03.', 'arg' => '2026-03'],
	], ClientRuleExporter::export($month->getRules()));
	Assert::same([
		['op' => ':max', 'msg' => 'Please enter a value less than or equal to 10:30.', 'arg' => '10:30'],
	], ClientRuleExporter::export($time->getRules()));
});
