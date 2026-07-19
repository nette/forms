<?php declare(strict_types=1);

/**
 * Example: Form with Repeater (dynamic containers)
 */

if (@!include __DIR__ . '/../vendor/autoload.php') { // @ dependencies may not be installed
	die('Install packages using `composer install`');
}

use Nette\Forms\{Container, Form};

Tracy\Debugger::enable();


$form = new Form;

// Add repeater for persons
$persons = $form->addRepeater('persons', function (Container $person) {
	$person->addText('firstname', 'Jméno:')
		->setRequired();

	$person->addText('surname', 'Příjmení:')
		->setRequired();

	// Nested repeater for emails
	$person->addRepeater('emails', function (Container $email) {
		$email->addEmail('email', 'Email:')
			->setRequired();
	})->setBounds(min: 1, max: 3);
});

$persons->setBounds(min: 1, max: 5, default: 2);

// Customize the client-side buttons (optional)
$persons->getAddButtonPrototype()
	->setText('Přidat osobu')
	->class('btn btn-success');

$persons->getRemoveButtonPrototype()
	->setText('Odebrat')
	->class('btn btn-danger');

$form->addSubmit('submit', 'Odeslat');


$form->setDefaults([
	'persons' => [
		[
			'firstname' => 'Jan',
			'surname' => 'Novák',
			'emails' => [
				['email' => 'jan@example.com'],
				['email' => 'jan.novak@example.com'],
			],
		],
		[
			'firstname' => 'Marie',
			'surname' => 'Nováková',
			'emails' => [
				['email' => 'marie@example.com'],
			],
		],
	],
]);


if ($form->isSuccess()) {
	echo '<h2>Odeslaná data:</h2>';
	Tracy\Dumper::dump($form->getValues());
	exit;
}


$latte = new Latte\Engine;
$latte->addExtension(new Nette\Bridges\FormsLatte\FormsExtension);
$latte->setCacheDirectory(__DIR__ . '/temp');

$latte->render(__DIR__ . '/repeater-template.latte', ['form' => $form]);
