<?php declare(strict_types=1);

use Nette\Forms\{Form, Validation};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('removing specific validation rules', function () {
	$form = new Form;
	$input = $form->addText('text1');
	$input->setRequired();
	$input->addRule($form::Email);

	$rules = iterator_to_array($input->getRules());
	Assert::count(2, $rules);
	Assert::same($form::Required, $rules[0]->validator);
	Assert::same($form::Email, $rules[1]->validator);

	$input->getRules()->removeRule($form::Email);

	$rules = iterator_to_array($input->getRules());
	Assert::count(1, $rules);
	Assert::same($form::Required, $rules[0]->validator);

	$input->getRules()->removeRule($form::Required);

	$rules = iterator_to_array($input->getRules());
	Assert::count(0, $rules);
});


test('removing non-existent rule does nothing', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->setRequired();

	Assert::count(1, iterator_to_array($input->getRules()));
	$input->getRules()->removeRule($form::Email);

	Assert::count(1, iterator_to_array($input->getRules()));
});


test('removal does not depend on the declaration style', function () {
	$countRules = fn($input) => count(iterator_to_array($input->getRules()));
	$form = new Form;

	$input = $form->addText('a')->addRule(Form::Email);
	$input->getRules()->removeRule(Validation\EmailValidator::class);
	Assert::same(0, $countRules($input));

	$input = $form->addText('b')->addRule(new Validation\EmailValidator);
	$input->getRules()->removeRule(Form::Email);
	Assert::same(0, $countRules($input));

	$input = $form->addText('c')->setRequired();
	$input->getRules()->removeRule(Validation\FilledValidator::class);
	Assert::false($input->isRequired());

	$input = $form->addText('d')->addRule(new Validation\FilledValidator);
	$input->getRules()->removeRule(Form::Filled);
	Assert::false($input->isRequired());
});


test('an instance removes only itself', function () {
	$form = new Form;
	$first = new Validation\PatternValidator('a+');
	$second = new Validation\PatternValidator('b+');
	$input = $form->addText('a')->addRule($first)->addRule($second);

	$input->getRules()->removeRule($first);
	$rules = iterator_to_array($input->getRules());
	Assert::count(1, $rules);
	Assert::same($second, $rules[0]->validator);
});


test('an operation sharing its class with others removes only the rules declared with it', function () {
	$form = new Form;
	$input = $form->addText('a')
		->addRule(Form::Min, null, 1)
		->addRule(Form::Max, null, 9)
		->addRule(new Validation\RangeValidator(min: 2));

	$input->getRules()->removeRule(Form::Min);
	$rules = iterator_to_array($input->getRules());
	Assert::count(2, $rules);
	Assert::same(Form::Max, $rules[0]->validator);

	$input->getRules()->removeRule(Validation\RangeValidator::class);
	Assert::count(0, iterator_to_array($input->getRules()));
});


test('conditional rule removal handling', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::Email);

	Assert::count(1, iterator_to_array($input->getRules()));
	$input->getRules()->removeRule($form::Email);

	Assert::count(1, iterator_to_array($input->getRules()));
});
