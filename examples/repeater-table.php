<?php declare(strict_types=1);

/**
 * Example: Repeater rendered as table rows (invoice items)
 */

if (@!include __DIR__ . '/../vendor/autoload.php') { // @ dependencies may not be installed
	die('Install packages using `composer install`');
}

use Nette\Forms\{Container, Form};

Tracy\Debugger::enable();


$form = new Form;

$items = $form->addRepeater('items', function (Container $item) {
	$item->addText('description', 'Popis:')
		->setRequired();

	$item->addInteger('quantity', 'Množství:')
		->setDefaultValue(1)
		->addRule(Form::Min, 'Množství musí být alespoň %d.', 1);

	$item->addFloat('unitPrice', 'Cena/ks:')
		->setRequired();
});

$items->setBounds(min: 1, max: 10, default: 3);

// the repeater renders as table rows
$items->getContainerPrototype()->setName('tbody');
$items->getItemPrototype()->setName('tr');

$items->getAddButtonPrototype()
	->setText('+ Přidat řádek')
	->class('btn btn-success');

$form->addSubmit('submit', 'Uložit fakturu');


if ($form->isSuccess()) {
	echo '<h2>Odeslaná data:</h2>';
	Tracy\Dumper::dump($form->getValues());
	exit;
}


$latte = new Latte\Engine;
$latte->addExtension(new Nette\Bridges\FormsLatte\FormsExtension);
$latte->setCacheDirectory(__DIR__ . '/temp');

$latte->render(__DIR__ . '/repeater-table.latte', ['form' => $form]);
