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
	$latte->compile(__DIR__ . '/templates/forms.scope.latte'),
);

Assert::matchFile(
	__DIR__ . '/expected/forms.scope.html',
	$latte->renderToString(__DIR__ . '/templates/forms.scope.latte'),
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
		fn() => $latte->compile('{formContext myForm, class: x}{/formContext}'),
		Latte\CompileException::class,
		'Arguments are not allowed in {formContext}%a%',
	);
});
