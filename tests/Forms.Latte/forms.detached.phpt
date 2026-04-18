<?php declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Form('myForm');
$form->addHidden('id', '42');
$form->addText('name', 'Name:');
$form->addText('email', 'Email:');
$form->addSubmit('submit', 'Save');

$latte = new Latte\Engine;
$latte->addExtension(new FormsExtension);
$latte->addProvider('uiControl', ['myForm' => $form]);

Assert::matchFile(
	__DIR__ . '/expected/forms.detached.php',
	$latte->compile(__DIR__ . '/templates/forms.detached.latte'),
);

Assert::matchFile(
	__DIR__ . '/expected/forms.detached.html',
	$latte->renderToString(__DIR__ . '/templates/forms.detached.latte'),
);


test('detached requires a Form instance, not a plain Container', function () {
	$container = new Nette\Forms\Container;
	$container->addText('name');
	$latte = new Latte\Engine;
	$latte->setLoader(new Latte\Loaders\StringLoader);
	$latte->addExtension(new FormsExtension);
	$latte->addProvider('uiControl', ['myCont' => $container]);

	Assert::exception(
		fn() => $latte->renderToString("{form detached myCont}{/form}\n"),
		Nette\InvalidStateException::class,
		'Detached mode requires a Form instance.',
	);
});


test('detached form must have an id', function () {
	$form = new Nette\Forms\Form; // no name → no id
	$form->addText('name');
	$latte = new Latte\Engine;
	$latte->setLoader(new Latte\Loaders\StringLoader);
	$latte->addExtension(new FormsExtension);
	$latte->addProvider('uiControl', ['noName' => $form]);

	Assert::exception(
		fn() => $latte->renderToString("{form detached noName}{/form}\n"),
		Nette\InvalidStateException::class,
		'Detached form must have an id%a%',
	);
});
