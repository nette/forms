<?php

/**
 * Nette Forms custom rendering example.
 */


if (@!include __DIR__ . '/../vendor/autoload.php') {
	die('Install packages using `composer install`');
}

use Nette\Forms\Form;
use Tracy\Debugger;
use Tracy\Dumper;
use Nette\Utils\Html;

Debugger::enable();


$form = new Form;
// setup custom rendering
$renderer = $form->getRenderer();
$renderer->wrappers['form']['container'] = Html::el('div')->id('form');
$renderer->wrappers['group']['container'] = NULL;
$renderer->wrappers['group']['label'] = 'h3';
$renderer->wrappers['pair']['container'] = NULL;
$renderer->wrappers['controls']['container'] = 'dl';
$renderer->wrappers['control']['container'] = 'dd';
$renderer->wrappers['control']['.odd'] = 'odd';
$renderer->wrappers['label']['container'] = 'dt';
$renderer->wrappers['label']['suffix'] = ':';
$renderer->wrappers['control']['requiredsuffix'] = " \xE2\x80\xA2";


$form->addGroup('Personal data');
$form->addText('name', 'Your name')
	->setRequired('Enter your name');

$form->addRadioList('gender', 'Your gender', array(
	'm' => Html::el('option', 'male')->style('color: #248bd3'),
	'f' => Html::el('option', 'female')->style('color: #e948d4'),
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


if ($form->isSuccess()) {
	echo '<h2>Form was submitted and successfully validated</h2>';
	Dumper::dump($form->getValues());
	exit;
}


?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Nette Forms custom rendering example</title>

<link rel="stylesheet" media="screen" href="assets/style.css" />
<style>
	textarea, select, input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="image"]):not([type="range"]) {
		border: 1px solid #78BD3F;
	}

	dt, dd {
		padding: .5em 1em;
	}

	#form h3 {
		background: #78BD3F;
		color: white;
		margin: 0;
		padding: .1em 1em;
		font-size: 100%;
		font-weight: normal;
		clear: both;
	}

	#form dl {
		background: #F8F8F8;
		margin: 0;
	}

	#form dt {
		text-align: right;
		font-weight: normal;
		float: left;
		width: 10em;
		clear: both;
	}

	#form dd {
		margin: 0;
		padding-left: 10em;
		display: block;
	}

	#form dd ul {
		list-style: none;
		font-size: 90%;
	}

	#form dd.odd {
		background: #EEE;
	}
</style>
<script src="http://nette.github.io/resources/js/netteForms.js"></script>

<h1>Nette Forms custom rendering example</h1>

<?php echo $form ?>

<footer><a href="http://doc.nette.org/en/forms">see documentation</a></footer>
