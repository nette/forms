<?php

/**
 * Nette Forms & Bootstap 3 rendering example.
 */


if (@!include __DIR__ . '/../vendor/autoload.php') {
	die('Install packages using `composer install`');
}

use Nette\Forms\Form;
use Nette\Forms\Controls;
use Tracy\Debugger;
use Tracy\Dumper;

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
$renderer->wrappers['pair']['container'] = 'div class=form-group';
$renderer->wrappers['pair']['.error'] = 'has-error';
$renderer->wrappers['control']['container'] = 'div class=col-sm-9';
$renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
$renderer->wrappers['control']['description'] = 'span class=help-block';
$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

// make form and controls compatible with Twitter Bootstrap
$form->getElementPrototype()->class('form-horizontal');

foreach ($form->getControls() as $control) {
	if ($control instanceof Controls\Button) {
		$control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
		$usedPrimary = TRUE;

	} elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
		$control->getControlPrototype()->addClass('form-control');

	} elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
		$control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
	}
}


?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Nette Forms & Bootstrap 3 rendering example</title>

<link rel="stylesheet" media="screen" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" />
<style>
form {
	max-width: 50em;
}
</style>

<div class="container">
	<div class="page-header">
		<h1>Nette Forms & Bootstrap 3 rendering example</h1>
	</div>

	<?php echo $form ?>
</div>
