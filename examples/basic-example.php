<?php

/**
 * Nette Forms basic example.
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

// group Personal data
$form->addGroup('Personal data')
	->setOption('description', 'We value your privacy and we ensure that the information you give to us will not be shared to other entities.');

$form->addText('name', 'Your name:')
	->setRequired('Enter your name');

$form->addText('age', 'Your age:')
	->setRequired('Enter your age')
	->addRule($form::INTEGER, 'Age must be numeric value')
	->addRule($form::RANGE, 'Age must be in range from %d to %d', array(10, 100));

$form->addRadioList('gender', 'Your gender:', array(
	'm' => 'male',
	'f' => 'female',
));

$form->addCheckboxList('colors', 'Favorite colors:', array(
	'r' => 'red',
	'g' => 'green',
	'b' => 'blue',
));

$form->addText('email', 'Email:')
	->setEmptyValue('@')
	->addCondition($form::FILLED) // conditional rule: if is email filled, ...
		->addRule($form::EMAIL, 'Incorrect email address'); // ... then check email


// group Shipping address
$form->addGroup('Shipping address')
	->setOption('embedNext', TRUE);

$form->addCheckbox('send', 'Ship to address')
	->addCondition($form::FILLED) // conditional rule: if is checkbox checked...
		->toggle('sendBox'); // toggle div #sendBox


// subgroup
$form->addGroup()
	->setOption('container', Html::el('div')->id('sendBox'));

$form->addText('street', 'Street:');

$form->addText('city', 'City:')
	->addConditionOn($form['send'], $form::FILLED)
		->setRequired('Enter your shipping address');

$countries = array(
	'World' => array(
		'bu' => 'Buranda',
		'qu' => 'Qumran',
		'st' => 'Saint Georges Island',
	),
	'?' => 'other',
);
$form->addSelect('country', 'Country:', $countries)
	->setPrompt('Select your country')
	->addConditionOn($form['send'], $form::FILLED)
		->setRequired('Select your country');


// group Your account
$form->addGroup('Your account');

$form->addPassword('password', 'Choose password:')
	->setRequired('Choose your password')
	->addRule($form::MIN_LENGTH, 'The password is too short: it must be at least %d characters', 3);

$form->addPassword('password2', 'Reenter password:')
	->setRequired('Reenter your password')
	->addRule($form::EQUAL, 'Passwords do not match', $form['password']);

$form->addUpload('avatar', 'Picture:')
	->addCondition($form::FILLED)
		->addRule($form::IMAGE, 'Uploaded file is not image');

$form->addHidden('userid');

$form->addTextArea('note', 'Comment:');

// group for buttons
$form->addGroup();

$form->addSubmit('submit', 'Send');


$form->setDefaults(array(
	'name' => 'John Doe',
	'userid' => 231,
));


if ($form->isSuccess()) {
	echo '<h2>Form was submitted and successfully validated</h2>';
	Dumper::dump($form->getValues());
	exit;
}


?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Nette Forms basic example</title>
<link rel="stylesheet" media="screen" href="assets/style.css" />
<script src="https://nette.github.io/resources/js/netteForms.js"></script>

<h1>Nette Forms basic example</h1>

<?php echo $form ?>

<footer><a href="https://doc.nette.org/en/forms">see documentation</a></footer>
