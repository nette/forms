<?php declare(strict_types=1);

/**
 * Example: Repeater rendered automatically by echo $form
 */

if (@!include __DIR__ . '/../vendor/autoload.php') { // @ dependencies may not be installed
	die('Install packages using `composer install`');
}

use Nette\Forms\{Container, Form};

Tracy\Debugger::enable();


$form = new Form;

$form->addRepeater('persons', function (Container $person) {
	$person->addText('name', 'Jméno:')
		->setRequired();
	$person->addEmail('email', 'Email:');
}, min: 1, max: 5);

$form->addSubmit('submit', 'Odeslat');


if ($form->isSuccess()) {
	echo '<h2>Odeslaná data:</h2>';
	Tracy\Dumper::dump($form->getValues());
	exit;
}


?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Repeater Example - Default Renderer</title>
<script src="https://unpkg.com/nette-forms@4/nette-forms.umd.js"></script>

<h1>Repeater - Default Renderer</h1>

<?php echo $form ?>
