<?php

/**
 * Test: Nette\Forms and toggle.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('nested condition toggles', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::Equal, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::Equal, 'x')
				->toggle('b');

	Assert::same([
		'a' => false,
		'b' => false,
	], $form->getToggles());
});


test('combined conditions for toggles', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::Equal, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('b');

	Assert::same([
		'a' => false,
		'b' => false,
	], $form->getToggles());
});


test('inverse condition activation', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::Equal, 'x')
				->toggle('b');

	Assert::same([
		'a' => true,
		'b' => false,
	], $form->getToggles());
});


test('double inverse conditions', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('b');

	Assert::same([
		'a' => true,
		'b' => true,
	], $form->getToggles());
});


test('independent toggle conditions', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::Equal, 'x')
			->toggle('a')
		->endCondition()
		->addConditionOn($form['2'], Form::Equal, 'x')
			->toggle('b');

	Assert::same([
		'a' => false,
		'b' => false,
	], $form->getToggles());
});


test('mixed toggle conditions', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::Equal, 'x')
			->toggle('a')
		->endCondition()
		->addConditionOn($form['2'], Form::NotEqual, 'x')
			->toggle('b');

	Assert::same([
		'a' => false,
		'b' => true,
	], $form->getToggles());
});


test('separate condition chains', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->toggle('a')
		->endCondition()
		->addConditionOn($form['2'], Form::Equal, 'x')
			->toggle('b');

	Assert::same([
		'a' => true,
		'b' => false,
	], $form->getToggles());
});


test('independent inverse conditions', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->toggle('a')
		->endCondition()
		->addConditionOn($form['2'], Form::NotEqual, 'x')
			->toggle('b');

	Assert::same([
		'a' => true,
		'b' => true,
	], $form->getToggles());
});


test('basic toggle conditions', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::Equal, 'x')
			->toggle('a');
	$form->addText('2')
		->addCondition(Form::Equal, 'x')
			->toggle('b');

	Assert::same([
		'a' => false,
		'b' => false,
	], $form->getToggles());
});


test('mixed basic conditions', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::Equal, 'x')
			->toggle('a');
	$form->addText('2')
		->addCondition(Form::NotEqual, 'x')
			->toggle('b');

	Assert::same([
		'a' => false,
		'b' => true,
	], $form->getToggles());
});


test('inverse and direct conditions', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::NotEqual, 'x')
			->toggle('a');
	$form->addText('2')
		->addCondition(Form::Equal, 'x')
			->toggle('b');

	Assert::same([
		'a' => true,
		'b' => false,
	], $form->getToggles());
});


test('dual inverse conditions', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::NotEqual, 'x')
			->toggle('a');
	$form->addText('2')
		->addCondition(Form::NotEqual, 'x')
			->toggle('b');

	Assert::same([
		'a' => true,
		'b' => true,
	], $form->getToggles());
});


test('overlapping toggle IDs', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::Equal, 'x')
			->toggle('a');
	$form->addText('2')
		->addCondition(Form::Equal, 'x')
			->toggle('a');

	Assert::same([
		'a' => false,
	], $form->getToggles());
});


test('conflicting toggle IDs', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::Equal, 'x')
			->toggle('a');
	$form->addText('2')
		->addCondition(Form::NotEqual, 'x')
			->toggle('a');

	Assert::same([
		'a' => true,
	], $form->getToggles());
});


test('inverse toggle ID conflict', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::NotEqual, 'x')
			->toggle('a');
	$form->addText('2')
		->addCondition(Form::Equal, 'x')
			->toggle('a');

	Assert::same([
		'a' => true,
	], $form->getToggles());
});


test('consistent inverse toggles', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::NotEqual, 'x')
			->toggle('a');
	$form->addText('2')
		->addCondition(Form::NotEqual, 'x')
			->toggle('a');

	Assert::same([
		'a' => true,
	], $form->getToggles());
});


test('nested same-toggle conditions', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::Equal, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::Equal, 'x')
				->toggle('a');

	Assert::same([
		'a' => false,
	], $form->getToggles());
});


test('mixed nested toggles', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::Equal, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('a');

	Assert::same([
		'a' => false,
	], $form->getToggles());
});


test('inverse condition chaining', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::Equal, 'x')
				->toggle('a');

	Assert::same([
		'a' => true,
	], $form->getToggles());
});


test('dual inverse nested conditions', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('a');

	Assert::same([
		'a' => true,
	], $form->getToggles());
});


test('toggle visibility options', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::Equal, 'x')
			->toggle('a', hide: false)
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('b');

	Assert::same([
		'a' => true,
		'b' => false,
	], $form->getToggles());
});


test('force-show toggles', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::Equal, 'x')
			->toggle('a', hide: false)
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('b', hide: false);

	Assert::same([
		'a' => true,
		'b' => true,
	], $form->getToggles());
});


test('mixed visibility modes', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->toggle('a', hide: false)
			->addConditionOn($form['2'], Form::Equal, 'x')
				->toggle('b', hide: false);

	Assert::same([
		'a' => false,
		'b' => true,
	], $form->getToggles());
});


test('visibility and basic toggles', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::Equal, 'x')
			->toggle('a', hide: false);
	$form->addText('2')
		->addCondition(Form::NotEqual, 'x')
			->toggle('b');

	Assert::same([
		'a' => true,
		'b' => true,
	], $form->getToggles());
});


test('mixed toggle visibility', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::Equal, 'x')
			->toggle('a');
	$form->addText('2')
		->addCondition(Form::NotEqual, 'x')
			->toggle('b', hide: false);

	Assert::same([
		'a' => false,
		'b' => false,
	], $form->getToggles());
});


test('force-show multiple toggles', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::Equal, 'x')
			->toggle('a', hide: false);
	$form->addText('2')
		->addCondition(Form::Equal, 'x')
			->toggle('b', hide: false);

	Assert::same([
		'a' => true,
		'b' => true,
	], $form->getToggles());
});


test('toggle visibility chaining', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::Equal, 'x')
			->toggle('a', hide: false)
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('a');

	Assert::same([
		'a' => true,
	], $form->getToggles());
});


test('force-show nested toggles', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::Equal, 'x')
			->toggle('a', hide: false)
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('a', hide: false);

	Assert::same([
		'a' => true,
	], $form->getToggles());
});


test('inverse visibility chaining', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->toggle('a', hide: false)
			->addConditionOn($form['2'], Form::Equal, 'x')
				->toggle('a', hide: false);

	Assert::same([
		'a' => true,
	], $form->getToggles());
});


test('visibility with global toggles', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::Equal, 'x')
			->toggle('a', hide: false);
	$form->addText('2')
		->addCondition(Form::NotEqual, 'x')
			->toggle('b');

	Assert::same([
		'a' => true,
		'b' => true,
	], $form->getToggles());
});


test('conflicting visibility modes', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::Equal, 'x')
			->toggle('a');
	$form->addText('2')
		->addCondition(Form::NotEqual, 'x')
			->toggle('b', hide: false);

	Assert::same([
		'a' => false,
		'b' => false,
	], $form->getToggles());
});


test('overlapping visibility IDs', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::Equal, 'x')
			->toggle('a', hide: false);
	$form->addText('2')
		->addCondition(Form::Equal, 'x')
			->toggle('a', hide: false);

	Assert::same([
		'a' => true,
	], $form->getToggles());
});


test('conflicting visibility IDs', function () {
	$form = new Form;
	$form->addText('1')
		->addCondition(Form::Equal, 'x')
			->toggle('a', hide: false);
	$form->addText('2')
		->addCondition(Form::NotEqual, 'x')
			->toggle('a', hide: false);

	Assert::same([
		'a' => true,
	], $form->getToggles());
});


test('toggle with endCondition', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->setRequired()
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('b')
			->endCondition()
		->endCondition();

	Assert::same([
		'a' => false,
		'b' => false,
	], $form->getToggles());
});


test('toggles with validation rules', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->setRequired()
		->addRule(Form::NotEqual, null, 'x')
		->addConditionOn($form['1'], Form::Equal, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::Equal, 'x')
				->toggle('b');

	Assert::same([
		'a' => false,
		'b' => false,
	], $form->getToggles());
});


test('validation and inverse conditions', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->setRequired()
		->addRule(Form::Equal, null, 'x')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('b');

	Assert::same([
		'a' => false,
		'b' => false,
	], $form->getToggles());
});


test('conditional required fields', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->setRequired()
			->addRule(Form::Equal, null, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('b');

	Assert::same([
		'a' => true,
		'b' => false,
	], $form->getToggles());
});


test('rule-based toggle activation', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addRule(Form::Equal, null, 'x')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('b');

	Assert::same([
		'a' => true,
		'b' => true,
	], $form->getToggles());
});


test('chained rules and toggles', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->addRule(Form::Equal, null, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('b');

	Assert::same([
		'a' => true,
		'b' => true,
	], $form->getToggles());
});


test('complex rule-based toggles', function () {
	$form = new Form;
	$form->addText('1');
	$form->addText('2');
	$form->addText('3')
		->addConditionOn($form['1'], Form::NotEqual, 'x')
			->addRule(Form::Equal, null, 'x')
			->toggle('a')
			->addConditionOn($form['2'], Form::NotEqual, 'x')
				->toggle('b');

	Assert::same([
		'a' => true,
		'b' => true,
	], $form->getToggles());
});
