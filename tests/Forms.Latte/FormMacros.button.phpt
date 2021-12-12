<?php

/**
 * Test: FormMacros.
 */

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormMacros;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addSubmit('send', 'Sign in');

$latte = new Latte\Engine;
FormMacros::install($latte->getCompiler());
$latte->addProvider('uiControl', ['myForm' => $form]);

Assert::matchFile(
	__DIR__ . '/expected/FormMacros.button.phtml',
	$latte->compile(__DIR__ . '/templates/forms.button.latte'),
);

Assert::matchFile(
	__DIR__ . '/expected/FormMacros.button.html',
	$latte->renderToString(__DIR__ . '/templates/forms.button.latte'),
);
