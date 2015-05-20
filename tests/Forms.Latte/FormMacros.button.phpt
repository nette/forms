<?php

/**
 * Test: FormMacros.
 */

use Nette\Forms\Form,
	Nette\Bridges\FormsLatte\FormMacros,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addSubmit('send', 'Sign in');

$latte = new Latte\Engine;
FormMacros::install($latte->getCompiler());

Assert::matchFile(
	__DIR__ . '/expected/FormMacros.button.phtml',
	$latte->compile(__DIR__ . '/templates/forms.button.latte')
);

Assert::matchFile(
	__DIR__ . '/expected/FormMacros.button.html',
	$latte->renderToString(
		__DIR__ . '/templates/forms.button.latte',
		['_control' => ['myForm' => $form]]
	)
);
