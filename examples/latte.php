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
$form->addText('name', 'Your name:')
	->setRequired('Enter your name');

$form->addPassword('password', 'Choose password:')
	->setRequired('Choose your password')
	->addRule($form::MinLength, 'The password is too short: it must be at least %d characters', 3);

$form->addPassword('password2', 'Reenter password:')
	->setRequired('Reenter your password')
	->addRule($form::Equal, 'Passwords do not match', $form['password']);

$form->addSubmit('submit', 'Send');

if ($form->isSuccess()) {
	echo '<h2>Form was submitted and successfully validated</h2>';
	Dumper::dump($form->getValues());
	exit;
}

$latte = new Latte\Engine;
$latte->addExtension(new Nette\Bridges\FormsLatte\FormsExtension);

$latte->render(__DIR__ . '/latte/page.latte', ['form' => $form]);
