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


test('length validator exports two client rules with own messages and sets maxlength', function () {
	$form = new Form;
	$input = $form->addText('name');
	$input->addRule(new Validation\LengthValidator(min: 2, max: 5));
	Assert::same([
		['op' => ':minLength', 'msg' => 'Please enter at least 2 characters.', 'arg' => 2],
		['op' => ':maxLength', 'msg' => 'Please enter no more than 5 characters.', 'arg' => 5],
	], ClientRuleExporter::export($input->getRules()));
	Assert::same(5, $input->getControl()->maxlength);
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


test('user message override propagates to all exported client rules', function () {
	$form = new Form;
	$input = $form->addText('name');
	$input->addRule(new Validation\LengthValidator(min: 2, max: 5), 'Length must be right');
	Assert::same([
		['op' => ':minLength', 'msg' => 'Length must be right', 'arg' => 2],
		['op' => ':maxLength', 'msg' => 'Length must be right', 'arg' => 5],
	], ClientRuleExporter::export($input->getRules()));
});


test('server-only validator object is skipped and the export continues', function () {
	$serverOnly = new class implements Validation\Validator {
		public function validate(mixed $value): ?Validation\Message
		{
			return null;
		}


		public function getClientRules(): array
		{
			return [];
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
