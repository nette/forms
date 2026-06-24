<?php declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addText('name', 'Name:');
$person = $form->addContainer('person');
$person->addText('city', 'City:');

$latte = new Latte\Engine;
$latte->addExtension(new FormsExtension);
$latte->addProvider('uiControl', ['myForm' => $form]);

Assert::matchFile(
	__DIR__ . '/expected/forms.scope.php',
	@$latte->compile(__DIR__ . '/templates/forms.scope.latte'), // @ {formContext} and {formContainer} are deprecated
);

Assert::matchFile(
	__DIR__ . '/expected/forms.scope.html',
	@$latte->renderToString(__DIR__ . '/templates/forms.scope.latte'), // @ {formContext} and {formContainer} are deprecated
);


test('arguments are rejected in {form scope} (no <form> to apply them to)', function () {
	$latte = new Latte\Engine;
	$latte->setLoader(new Latte\Loaders\StringLoader);
	$latte->addExtension(new FormsExtension);

	Assert::exception(
		fn() => $latte->compile('{form scope myForm, class: x}{/form}'),
		Latte\CompileException::class,
		'Arguments are not allowed in {form scope}%a%',
	);
});


test('arguments are rejected in {formContext}', function () {
	$latte = new Latte\Engine;
	$latte->setLoader(new Latte\Loaders\StringLoader);
	$latte->addExtension(new FormsExtension);

	Assert::exception(
		fn() => @$latte->compile('{formContext myForm, class: x}{/formContext}'), // @ {formContext} is deprecated
		Latte\CompileException::class,
		'Arguments are not allowed in {formContext}%a%',
	);
});


test('$form is restored after leaving a nested scope', function () {
	$form = new Form;
	$form->addText('name');
	$form->addContainer('person')->addText('city');

	$latte = new Latte\Engine;
	$latte->setLoader(new Latte\Loaders\StringLoader);
	$latte->addExtension(new FormsExtension);
	$latte->addProvider('uiControl', ['myForm' => $form]);

	$probe = new stdClass;
	$latte->renderToString(
		'{form myForm}{form scope person}{do $probe->inner = $form}{/form}{do $probe->outer = $form}{/form}{do $probe->after = $form}',
		['probe' => $probe],
	);
	Assert::same($form['person'], $probe->inner);
	Assert::same($form, $probe->outer);
	Assert::same($form, $probe->after); // top level keeps pointing at the closed form (BC)
});


test('$form skips {formContainer} scope when restored after a nested detached form', function () {
	$outer = new Form;
	$outer->addContainer('person')->addText('city');
	$login = new Form('login');
	$login->addText('name');

	$latte = new Latte\Engine;
	$latte->setLoader(new Latte\Loaders\StringLoader);
	$latte->addExtension(new FormsExtension);
	$latte->addProvider('uiControl', ['outer' => $outer, 'login' => $login]);

	$probe = new stdClass;
	@$latte->renderToString( // @ {formContainer} is deprecated
		'{form outer}{formContainer person}{form detached login}{/form}{do $probe->inContainer = $form}{/formContainer}{/form}',
		['probe' => $probe],
	);
	Assert::same($outer, $probe->inContainer); // the Container scope is not bound to $form
});
