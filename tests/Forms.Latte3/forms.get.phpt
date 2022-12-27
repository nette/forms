<?php

/** @phpVersion 8.0 */

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


$form = new Form;
$form->setMethod('get');
$form->setAction('?arg=val');
$form->addSubmit('send', 'Sign in');

$latte = new Latte\Engine;
$latte->addExtension(new FormsExtension);
$latte->addProvider('uiControl', ['myForm' => $form]);

Assert::matchFile(
	__DIR__ . '/expected/forms.get.php',
	$latte->compile(__DIR__ . '/templates/forms.get.latte'),
);
Assert::matchFile(
	__DIR__ . '/expected/forms.get.html',
	$latte->renderToString(__DIR__ . '/templates/forms.get.latte'),
);
