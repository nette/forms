<?php

/**
 * Nette Forms manual form rendering.
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
$form->addText('name')
	->setRequired('Enter your name');

$form->addText('age')
	->setRequired('Enter your age');

$form->addRadioList('gender', null, [
	'm' => 'male',
	'f' => 'female',
]);

$form->addEmail('email');

$form->addSubmit('submit');

if ($form->isSuccess()) {
	echo '<h2>Form was submitted and successfully validated</h2>';
	Dumper::dump($form->getValues());
	exit;
}


?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Nette Forms manual form rendering</title>
	<link rel="stylesheet" media="screen" href="assets/style.css" />
	<script src="https://nette.github.io/resources/js/3/netteForms.js"></script>
</head>

<body>
	<h1>Nette Forms manual form rendering</h1>

	<?php $form->render('begin') ?>

	<?php if ($form->errors) { ?>
	<ul class="error">
		<?php foreach ($form->errors as $error) { ?>
		<li><?php echo htmlspecialchars($error) ?></li>
		<?php } ?>
	</ul>
	<?php } ?>

	<fieldset>
		<legend>Personal data</legend>
		<table>
		<tr class="required">
			<th><?php $form->render()['name']->getLabel('Your name:') ?></th>
			<td><?php $form->render()['name']->control->cols(35) ?> <?php $form->render()['name']->error ?></td>
		</tr>
		<tr class="required">
			<th><?php $form->render()['age']->getLabel('Your age:') ?></th>
			<td><?php $form->render()['age']->control->cols(5) ?> <?php $form->render()['age']->error ?></td>
		</tr>
		<tr>
			<th><?php $form->render()['gender']->getLabel('Your gender:') ?></th>
			<td><?php $form->render()['gender']->control ?> <?php $form->render()['gender']->error ?></td>
		</tr>
		<tr>
			<th><?php $form->render()['email']->getLabel('Email:') ?></th>
			<td><?php $form->render()['email']->control->cols(35) ?> <?php $form->render()['email']->error ?></td>
		</tr>
		</table>
	</fieldset>

	<div>
		<?php $form->render()['submit']->getControl('Send') ?>
	</div>

	<?php $form->render('end'); ?>
</body>
</html>
