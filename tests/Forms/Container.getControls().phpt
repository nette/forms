<?php declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->addText('name');
$form->addInteger('age');
$form->addContainer('cont')
	->addText('name');

$controls = $form->getControls();

$names = $values = [];
foreach ($controls as $name => $value) {
	$names[] = $name;
	$values[] = $value;
}

Assert::same(['name', 'age', 'name'], $names);
Assert::same([$form['name'], $form['age'], $form['cont-name']], $values);

// generator cannot be traversed again
Assert::exception(
	fn() => iterator_to_array($controls),
	\Throwable::class,
	'Cannot traverse an already closed generator',
);
