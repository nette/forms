<?php

/**
 * Nette Forms & Bootstap 2 rendering example.
 */


if (@!include __DIR__ . '/../vendor/autoload.php') {
	die('Install packages using `composer update --dev`');
}

use Nette\Forms\Form,
	Nette\Forms\Controls,
	Tracy\Debugger,
	Tracy\Dumper;

Debugger::enable();


$form = new Form;

$form->addGroup('Personal data');
$form->addText('name', 'Your name')
	->setRequired('Enter your name');

$form->addRadioList('gender', 'Your gender', array(
	'male', 'female',
));

$form->addCheckboxList('colors', 'Favorite colors:', array(
	'red', 'green', 'blue',
));

$form->addSelect('country', 'Country', array(
	'Buranda', 'Qumran', 'Saint Georges Island',
));

$form->addCheckbox('send', 'Ship to address');

$form->addGroup('Your account');
$form->addPassword('password', 'Choose password');
$form->addUpload('avatar', 'Picture');
$form->addTextArea('note', 'Comment');

$form->addGroup();
$form->addSubmit('submit', 'Send');
$form->addSubmit('cancel', 'Cancel');


if ($form->isSuccess()) {
	echo '<h2>Form was submitted and successfully validated</h2>';
	Dumper::dump($form->getValues());
	exit;
}



// setup form rendering
$renderer = $form->getRenderer();
$renderer->wrappers['controls']['container'] = NULL;
$renderer->wrappers['pair']['container'] = 'div class=control-group';
$renderer->wrappers['pair']['.error'] = 'error';
$renderer->wrappers['control']['container'] = 'div class=controls';
$renderer->wrappers['label']['container'] = 'div class=control-label';
$renderer->wrappers['control']['description'] = 'span class=help-inline';
$renderer->wrappers['control']['errorcontainer'] = 'span class=help-inline';

// make form and controls compatible with Twitter Bootstrap
$form->getElementPrototype()->class('form-horizontal');

foreach ($form->getControls() as $control) {
	if ($control instanceof Controls\Button) {
		$control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn');
		$usedPrimary = TRUE;

	} elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
		$control->getLabelPrototype()->addClass($control->getControlPrototype()->type);
		$control->getSeparatorPrototype()->setName(NULL);
	}
}


?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Nette Forms & Bootstrap 2 rendering example</title>

<link rel="stylesheet" media="screen" href="http://netdna.bootstrapcdn.com/bootstrap/2.3.2/css/bootstrap.min.css" />

<div class="container">
	<div class="page-header">
		<h1>Nette Forms & Bootstrap 2 rendering example</h1>
	</div>

	<?php echo $form ?>
</div>
