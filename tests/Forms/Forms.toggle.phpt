<?php

/**
 * Test: Nette\Forms and toggle.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('AND', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('OR', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('OR & two components', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('OR & multiple used ID', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('AND & multiple used ID', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('$hide = false', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('$hide = false & multiple used ID', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('combined with rules', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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


test('', function () {
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
