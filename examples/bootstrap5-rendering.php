<?php

/**
 * Nette Forms & Bootstap v5 rendering example.
 */

declare(strict_types=1);


if (@!include __DIR__ . '/../vendor/autoload.php') {
	die('Install packages using `composer install`');
}

use Nette\Forms\Form;
use Tracy\Debugger;
use Tracy\Dumper;

Debugger::enable();


function makeBootstrap5(Form $form): void
{
	$renderer = $form->getRenderer();
	$renderer->wrappers['controls']['container'] = null;
	$renderer->wrappers['pair']['container'] = 'div class="mb-3 row"';
	$renderer->wrappers['label']['container'] = 'div class="col-sm-3 col-form-label"';
	$renderer->wrappers['control']['container'] = 'div class=col-sm-9';
	$renderer->wrappers['control']['description'] = 'span class=form-text';
	$renderer->wrappers['control']['errorcontainer'] = 'span class=invalid-feedback';
	$renderer->wrappers['control']['.error'] = 'is-invalid';
	$renderer->wrappers['error']['container'] = 'div class="alert alert-danger"';

	foreach ($form->getControls() as $control) {
		$type = $control->getOption('type');
		if ($type === 'button') {
			$control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-secondary');
			$usedPrimary = true;

		} elseif (in_array($type, ['text', 'textarea', 'select', 'datetime', 'file'], true)) {
			$control->getControlPrototype()->addClass('form-control');

		} elseif (in_array($type, ['checkbox', 'radio'], true)) {
			if ($control instanceof Nette\Forms\Controls\Checkbox) {
				$control->getLabelPrototype()->addClass('form-check-label');
			} else {
				$control->getItemLabelPrototype()->addClass('form-check-label');
			}
			$control->getControlPrototype()->addClass('form-check-input');
			$control->getContainerPrototype()->setName('div')->addClass('form-check');

		} elseif ($type === 'color') {
			$control->getControlPrototype()->addClass('form-control form-control-color');
		}
	}
}


$form = new Form;
$form->onRender[] = 'makeBootstrap5';

$form->addGroup('Personal data');
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


?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Nette Forms & Bootstrap v5 rendering example</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

<div class="container">
	<h1>Nette Forms & Bootstrap v5 rendering example</h1>

	<?php $form->render() ?>
</div>
