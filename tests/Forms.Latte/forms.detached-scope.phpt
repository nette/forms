<?php declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$main = new Form('main');
$main->addText('name', 'Name:');
$address = $main->addContainer('address');
$address->addText('street', 'Street:');

$wrap = new Form('wrap');
$wrap->addText('title', 'Title:');

$side = new Form('side');
$side->addText('note', 'Note:');

$latte = new Latte\Engine;
$latte->addExtension(new FormsExtension);
$latte->addProvider('uiControl', ['main' => $main, 'wrap' => $wrap, 'side' => $side]);

Assert::matchFile(
	__DIR__ . '/expected/forms.detached-scope.php',
	$latte->compile(__DIR__ . '/templates/forms.detached-scope.latte'),
);

Assert::matchFile(
	__DIR__ . '/expected/forms.detached-scope.html',
	$latte->renderToString(__DIR__ . '/templates/forms.detached-scope.latte'),
);
