<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\ClientRuleExporter::export()
 */

use Nette\Forms\{ClientRuleExporter, Form};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('exporting basic form validation rules', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addRule(Form::Filled, null, []);
	Assert::same([
		[
			'op' => ':filled',
			'msg' => 'This field is required.',
		],
	], ClientRuleExporter::export($input->getRules()));
});


test('exporting email validation rules', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addRule(Form::Email);
	Assert::same([
		['op' => ':email', 'msg' => 'Please enter a valid email address.'],
	], ClientRuleExporter::export($input->getRules()));
});


test('combining required and email validation rules', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->setRequired();
	$input->addRule(Form::Email);
	Assert::same([
		['op' => ':filled', 'msg' => 'This field is required.'],
		['op' => ':email', 'msg' => 'Please enter a valid email address.'],
	], ClientRuleExporter::export($input->getRules()));
});


test('optional field with email validation', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->setRequired(false);
	$input->addRule(Form::Email);
	Assert::same([
		['op' => ':email', 'msg' => 'Please enter a valid email address.'],
	], ClientRuleExporter::export($input->getRules()));
});


test('conditional validation rule exports', function () {
	$form = new Form;
	$input1 = $form->addText('text1');
	$input2 = $form->addText('text2');
	$input2->setRequired(false);
	$input2->addConditionOn($input1, Form::Email)
		->setRequired()
		->addRule($form::Email);
	$input2->addConditionOn($input1, Form::Integer)
		->setRequired(false)
		->addRule($form::Email);

	Assert::same([
		[
			'op' => ':email',
			'rules' => [
				['op' => ':filled', 'msg' => 'This field is required.'],
				['op' => ':email', 'msg' => 'Please enter a valid email address.'],
			],
			'control' => 'text1',
		],
		[
			'op' => ':integer',
			'rules' => [
				['op' => ':email', 'msg' => 'Please enter a valid email address.'],
			],
			'control' => 'text1',
		],
	], ClientRuleExporter::export($input2->getRules()));
});


test('rule export with pattern and filters', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addRule(Form::Pattern, 'match pattern', '\d+');
	$input->addFilter(function () {});
	$input->setRequired(false);
	$input->addRule(Form::Email);
	Assert::same([
		['op' => ':pattern', 'msg' => 'match pattern', 'arg' => '\d+'],
	], ClientRuleExporter::export($input->getRules()));
});


test('filter inside a condition branch stops the outer export too', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition(Form::Filled)
		->addRule(Form::MinLength, null, 3)
		->addFilter(fn($value) => $value);
	$input->addRule(Form::Email);
	Assert::same([
		[
			'op' => ':filled',
			'rules' => [
				['op' => ':minLength', 'msg' => 'Please enter at least 3 characters.', 'arg' => 3],
			],
			'control' => 'text',
		],
	], ClientRuleExporter::export($input->getRules()));
});


test('condition branch containing only a filter exports nothing and stops the export', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition(Form::Filled)
		->addFilter(fn($value) => $value);
	$input->addRule(Form::Email);
	Assert::same([], ClientRuleExporter::export($input->getRules()));
});


test('server-only callable rule is skipped and the export continues', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addRule(fn() => true, 'server only');
	$input->addRule(Form::Email);
	Assert::same([
		['op' => ':email', 'msg' => 'Please enter a valid email address.'],
	], ClientRuleExporter::export($input->getRules()));
});


test('condition whose validator splits into several client rules exports the original operation', function () {
	$form = new Form;
	$text = $form->addText('text');
	$text->addCondition(Form::Length, [5, 10])
		->setRequired()
		->toggle('#x');
	Assert::same([
		[
			'op' => ':length',
			'rules' => [['op' => ':filled', 'msg' => 'This field is required.']],
			'control' => 'text',
			'toggle' => ['#x' => true],
			'arg' => [5, 10],
		],
	], ClientRuleExporter::export($text->getRules()));

	$list = $form->addCheckboxList('tags', items: ['a' => 'A', 'b' => 'B', 'c' => 'C']);
	$list->addCondition(Form::Count, [2, 3])
		->toggle('#extra');
	Assert::same([
		[
			'op' => ':length',
			'rules' => [],
			'control' => 'tags[]',
			'toggle' => ['#extra' => true],
			'arg' => [2, 3],
		],
	], ClientRuleExporter::export($list->getRules()));
});


