<?php declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addText('name', 'Name:');
$form->addText('email', 'Email:');

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
