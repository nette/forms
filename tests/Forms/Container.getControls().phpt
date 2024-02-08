<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addText('name');
$form->addInteger('age');
$form->addContainer('cont')
	->addText('name');

$controls = $form->getControls();
Assert::same([$form['name'], $form['age'], $form['cont-name']], $controls);
