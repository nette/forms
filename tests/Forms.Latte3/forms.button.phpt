<?php

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


$form = new Form;
$form->addSubmit('send', 'Sign in');

$latte = new Latte\Engine;
$latte->addExtension(new FormsExtension);
$latte->addProvider('uiControl', ['myForm' => $form]);

Assert::matchFile(
	__DIR__ . '/expected/forms.button.phtml',
	$latte->compile(__DIR__ . '/templates/forms.button.latte')
);

Assert::matchFile(
	__DIR__ . '/expected/forms.button.html',
	$latte->renderToString(__DIR__ . '/templates/forms.button.latte')
);
