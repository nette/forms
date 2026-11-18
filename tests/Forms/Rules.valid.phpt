<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Rules.
 */

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('condition based on another field\'s validity', function () {
	$form = new Form;
	$form->addText('foo')
		->setRequired('fill foo');
	$form->addText('bar')
		->addConditionOn($form['foo'], Form::Valid)
		->setRequired('fill bar');

	$form->validate();
	Assert::same(['fill foo'], $form->getErrors());

	$form['foo']->setValue('abc');
	$form->validate();
	Assert::same(['fill bar'], $form->getErrors());

	$form['bar']->setValue('abc');
	$form->validate();
	Assert::same([], $form->getErrors());
});


testException('exception on using Valid in addRule', function () {
	$form = new Form;
	$form->addText('foo')
		->addRule(Form::Valid);
}, Nette\InvalidArgumentException::class, 'You cannot use Form::Valid in the addRule method.');


test('filter application before validation', function () {
	$form = new Form;
	$form->addText('foo')
		->addFilter(fn($value) => str_replace(' ', '', $value))
		->addRule($form::Pattern, 'only numbers', '\d{5}');

	$form['foo']->setValue('160 00');
	$form->validate();
	Assert::same([], $form->getErrors());

	$form['foo']->setValue('160 00 x');
	$form->validate();
	Assert::same(['only numbers'], $form->getErrors());
});


test('filter and pattern validation interaction', function () {
	$form = new Form;
	$foo = $form->addText('foo');
	$rules = $foo->getRules();
	$rules->addFilter(
		fn($value) => str_replace(' ', '', $value),
	);
	$rules->addRule($form::Pattern, 'only numbers', '\d{5}');

	$form['foo']->setValue('160 00');
	$form->validate();
	Assert::same([], $form->getErrors());

	$form['foo']->setValue('160 00 x');
	$form->validate();
	Assert::same(['only numbers'], $form->getErrors());
});


test('toggles computed through a Valid condition neither apply filters nor add errors', function () {
	$form = new Form;
	$foo = $form->addText('foo')
		->setRequired('fill foo')
		->addFilter(fn($value) => $value . '!');
	$form->addText('bar')
		->addConditionOn($foo, Form::Valid)
			->toggle('#x');

	$foo->setValue('x');
	Assert::same(['#x' => true], $form->getToggles());
	Assert::same(['#x' => true], $form->getToggles());
	Assert::same('x', $foo->getValue());
	Assert::same([], $foo->getErrors());

	$foo->setValue('');
	Assert::same(['#x' => false], $form->getToggles());
	Assert::same([], $foo->getErrors());
});


test('Valid conditions referring to each other fail with a clear exception', function () {
	$form = new Form;
	$a = $form->addText('a');
	$b = $form->addText('b');
	$a->addConditionOn($b, Form::Valid)->setRequired();
	$b->addConditionOn($a, Form::Valid)->setRequired();

	Assert::exception(
		fn() => $form->validate(),
		Nette\InvalidStateException::class,
		"Form::Valid conditions of control '%a%' form a cycle.",
	);
	Assert::exception(
		fn() => $form->getToggles(),
		Nette\InvalidStateException::class,
		"Form::Valid conditions of control '%a%' form a cycle.",
	);
});


testException('exception on Valid in addCondition', function () {
	$form = new Form;
	$form->addText('foo')
		->addCondition(Form::Valid);
}, Nette\InvalidArgumentException::class, 'You cannot use Form::Valid in the addCondition method.');


testException('exception on negative Valid rule', function () {
	$form = new Form;
	$form->addText('foo')
		->addRule(~Form::Valid);
}, Nette\InvalidArgumentException::class, 'You cannot use Form::Valid in the addRule method.');
