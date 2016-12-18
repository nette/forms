<?php

/**
 * Nette Forms and HTML5.
 */


if (@!include __DIR__ . '/../vendor/autoload.php') {
	die('Install packages using `composer install`');
}

use Nette\Forms\Form;
use Tracy\Debugger;
use Tracy\Dumper;

Debugger::enable();


$form = new Form;

$form->addGroup();

$form->addText('query', 'Search:')
	->setHtmlType('search')
	->setHtmlAttribute('autofocus');

$form->addText('count', 'Number of results:')
	->setHtmlType('number')
	->setDefaultValue(10)
	->addRule($form::INTEGER, 'Must be numeric value')
	->addRule($form::RANGE, 'Must be in range from %d to %d', [1, 100]);

$form->addText('precision', 'Precision:')
	->setHtmlType('range')
	->setDefaultValue(50)
	->addRule($form::INTEGER, 'Precision must be numeric value')
	->addRule($form::RANGE, 'Precision must be in range from %d to %d', [0, 100]);

$form->addEmail('email', 'Send to email:')
	->setHtmlAttribute('autocomplete', 'off')
	->setHtmlAttribute('placeholder', 'Optional, but Recommended');

$form->addSubmit('submit', 'Send');


if ($form->isSuccess()) {
	echo '<h2>Form was submitted and successfully validated</h2>';
	Dumper::dump($form->getValues());
	exit;
}


?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Nette Forms and HTML5</title>
<link rel="stylesheet" media="screen" href="assets/style.css" />
<script src="https://nette.github.io/resources/js/netteForms.js"></script>

<h1>Nette Forms and HTML5</h1>

<?php echo $form ?>

<footer><a href="https://doc.nette.org/en/forms">see documentation</a></footer>
