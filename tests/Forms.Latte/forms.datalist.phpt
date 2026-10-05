<?php declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addText('city')->setSuggestions(['Praha', 'Brno']);
$form->addText('other')->setSuggestions(['X']);

$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new FormsExtension);
$latte->addProvider('uiControl', ['myForm' => $form]);

Assert::match(
	'<form action="" method="post"><input type="text" name="city" id="frm-city" list="frm-city-list"><datalist id="frm-city-list"><option value="Praha"></option><option value="Brno"></option></datalist></form>',
	$latte->renderToString('<form n:name=myForm><input n:name=city></form>'),
);
