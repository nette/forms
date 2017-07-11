<?php

/**
 * Nette Forms & Bootstap v2 rendering example.
 */


if (@!include __DIR__ . '/../vendor/autoload.php') {
	die('Install packages using `composer install`');
}

use Nette\Forms\Form;
use Tracy\Debugger;
use Tracy\Dumper;

Debugger::enable();


function makeBootstrap2(Form $form)
{
	$renderer = $form->getRenderer();
	$renderer->wrappers['controls']['container'] = null;
	$renderer->wrappers['pair']['container'] = 'div class=control-group';
	$renderer->wrappers['pair']['.error'] = 'error';
	$renderer->wrappers['control']['container'] = 'div class=controls';
	$renderer->wrappers['label']['container'] = 'div class=control-label';
	$renderer->wrappers['control']['description'] = 'span class=help-inline';
	$renderer->wrappers['control']['errorcontainer'] = 'span class=help-inline';
	$form->getElementPrototype()->class('form-horizontal');

	$form->onRender[] = function ($form) {
		foreach ($form->getControls() as $control) {
			$type = $control->getOption('type');
			if ($type === 'button') {
				$control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn');
				$usedPrimary = true;

			} elseif (in_array($type, ['checkbox', 'radio'], true)) {
				$control->getSeparatorPrototype()->setName('div')->addClass($type);
			}
		}
	};
}


$form = new Form;
makeBootstrap2($form);

$form->addGroup('Personal data');
$form->addText('name', 'Your name')
	->setRequired('Enter your name');

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
<title>Nette Forms & Bootstrap v2 rendering example</title>

<link rel="stylesheet" media="screen" href="https://netdna.bootstrapcdn.com/bootstrap/2.3.2/css/bootstrap.min.css" />

<div class="container">
	<div class="page-header">
		<h1>Nette Forms & Bootstrap v2 rendering example</h1>
	</div>

	<?php echo $form ?>
</div>
