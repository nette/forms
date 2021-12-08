<?php

/**
 * Test: FormMacros: {formContainer}
 */

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormMacros;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addText('input1', 'Input 1');

$cont1 = $form->addContainer('cont1');
$cont1->addText('input2', 'Input 2');
$cont1->addText('input3', 'Input 3');

$cont2 = $cont1->addContainer('cont2');
$cont2->addCheckbox('input4', 'Input 4');
$cont2->addCheckbox('input5', 'Input 5');
$cont2->addCheckbox('input6', 'Input 6');

$cont1->addText('input7', 'Input 7');

$contItems = $form->addContainer('items');
$items = [1, 3];
foreach ($items as $item) {
	$contItem = $contItems->addContainer($item);
	$contItem->addText('input', 'Input');
}

$form->addSubmit('input8', 'Input 8');


$latte = new Latte\Engine;
FormMacros::install($latte->getCompiler());
$latte->addProvider('uiControl', ['myForm' => $form]);

Assert::matchFile(
	__DIR__ . '/expected/FormMacros.formContainer.phtml',
	$latte->compile(__DIR__ . '/templates/forms.formContainer.latte'),
);
Assert::matchFile(
	__DIR__ . '/expected/FormMacros.formContainer.html',
	$latte->renderToString(__DIR__ . '/templates/forms.formContainer.latte'),
);
