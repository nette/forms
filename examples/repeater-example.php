<?php

/**
 * Example: Form with Repeater (dynamic containers)
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Nette\Forms\Form;
use Nette\Forms\Container;


Tracy\Debugger::enable();


// Create form
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

// Configure persons repeater
$persons->setBounds(min: 1, max: 5, default: 2);

// Add buttons (optional - for UI only)
/*
$persons->addCreateButton('Přidat osobu')
	->setHtmlAttribute('class', 'btn btn-success');

$persons->addRemoveButton('Odebrat')
	->setHtmlAttribute('class', 'btn btn-danger');
*/
$persons->defineButton('add', 'Přidat osobu') // $persons::RemoveButton
	->setHtmlAttribute('class', 'btn btn-success');

$persons->defineButton('remove', 'Odebrat')
	->setHtmlAttribute('class', 'btn btn-danger');

// Add submit button
$form->addSubmit('submit', 'Odeslat');


// Set default values
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


// Process form
//if ($form->isSuccess()) {
if ($form->isSubmitted()) {
	$values = $form->getValues();
	echo '<h2>Odeslaná data:</h2>';
	echo '<pre>';
	print_r($_POST);
	print_r($values);
	echo '</pre>';
	exit;
}


$latte = new Latte\Engine;
$latte->addExtension(new Nette\Bridges\FormsLatte\FormsExtension);
$latte->setTempDirectory('temp');
foreach (glob('temp/*') as $file) unlink($file);

$latte->render(__DIR__ . '/repeater-template.latte', ['form' => $form]);
exit;

// Render form
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Repeater Example</title>
	<style>
		body { font-family: sans-serif; margin: 2em; }
		.repeater-item { border: 1px solid #ccc; padding: 1em; margin-bottom: 1em; }
		.btn { padding: 0.5em 1em; margin: 0.25em; cursor: pointer; }
		.btn-success { background: #28a745; color: white; border: none; }
		.btn-danger { background: #dc3545; color: white; border: none; }
		label { display: block; margin: 0.5em 0; }
		input[type="text"], input[type="email"] { padding: 0.5em; width: 300px; }
	</style>
</head>
<body>
	<h1>Formulář s Repeater</h1>

	<?php $form->render() ?>

	<script>
		// TODO: JavaScript for dynamic add/remove functionality
		console.log('Repeater example loaded');
	</script>
</body>
</html>
