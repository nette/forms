<?php

/**
 * Nette Forms rendering using Latte.
 */

declare(strict_types=1);


if (@!include __DIR__ . '/../vendor/autoload.php') {
	die('Install packages using `composer install`');
}

use Nette\Forms\Form;
use Tracy\Debugger;
use Tracy\Dumper;

Debugger::enable();


$form = new Form;
$form->addText('name', 'Your name')
	->setRequired('Enter your name')
	->setOption('description', 'Name and surname');

$form->addDate('birth', 'Date of birth');

$form->addRadioList('gender', 'Your gender', [
	'male', 'female',
]);

$form->addCheckboxList('colors', 'Favorite colors', [
	'red', 'green', 'blue',
]);

$form->addSelect('country', 'Country', [
	'Buranda', 'Qumran', 'Saint Georges Island',
]);

$form->addCheckbox('send', 'Ship to address');

$form->addColor('color', 'Favourite colour');

$form->addPassword('password', 'Choose password');
$form->addUpload('avatar', 'Picture');
$form->addTextArea('note', 'Comment');

$form->addSubmit('submit', 'Send');
$form->addSubmit('cancel', 'Cancel');



if ($form->isSuccess()) {
	echo '<h2>Form was submitted and successfully validated</h2>';
	Dumper::dump($form->getValues());
	exit;
}

$latte = new Latte\Engine;
$latte->addExtension(new Nette\Bridges\FormsLatte\FormsExtension);

$latte->render(__DIR__ . '/latte/page.latte', ['form' => $form]);
