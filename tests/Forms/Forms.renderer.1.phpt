<?php

/**
 * Test: Nette\Forms default rendering.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';
$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';
$_POST = ['name' => 'John Doe ', 'age' => '', 'email' => '  @ ', 'send' => 'on', 'street' => '', 'city' => '', 'country' => 'HU', 'password' => 'xxx', 'password2' => '', 'note' => '', 'submit1' => 'Send', 'userid' => '231'];


$countries = [
	'Europe' => [
		'CZ' => 'Czech Republic',
		'SK' => 'Slovakia',
		'GB' => 'United Kingdom',
	],
	'CA' => 'Canada',
	'US' => 'United States',
	'?' => 'other',
];

$sex = [
	'm' => 'male',
	'f' => 'female',
];


$form = new Form;


$form->addGroup('Personal data')
	->setOption('description', 'We value your privacy and we ensure that the information you give to us will not be shared to other entities.')
	->setOption('id', 'test-group-id-set-via-option');

$form->addText('name', 'Your name:')
	->addRule(Form::FILLED, 'Enter your name')
	->setOption('class', 'myclass')
	->setOption('id', 'myid');

$form->addInteger('age', 'Your age:')
	->addRule(Form::FILLED, 'Enter your age')
	->addRule(Form::RANGE, 'Age must be in range from %d to %d', [10, 100]);

$form->addRadioList('gender', 'Your gender:', $sex);

$form->addEmail('email', 'Email:')
	->setEmptyValue('@');


$form->addGroup('Shipping address')
	->setOption('embedNext', true);

$form->addCheckbox('send', 'Ship to address')
	->addCondition(Form::EQUAL, false)
	->elseCondition()
		->toggle('sendBox');


$form->addGroup()
	->setOption('container', Html::el('div')->id('sendBox'));

$form->addText('street', 'Street:');

$form->addText('city', 'City:')
	->addConditionOn($form['send'], Form::EQUAL, true)
		->addRule(Form::FILLED, 'Enter your shipping address');

$form->addSelect('country', 'Country:', $countries)
	->setPrompt('Select your country')
	->addConditionOn($form['send'], Form::EQUAL, true)
		->addRule(Form::FILLED, 'Select your country');

$form->addSelect('countrySetItems', 'Country:')
	->setPrompt('Select your country')
	->setItems($countries);


$form->addGroup('Your account');

$form->addPassword('password', 'Choose password:')
	->setOption('nextTo', 'password2')
	->addRule(Form::FILLED, 'Choose your password')
	->addRule(Form::MIN_LENGTH, 'The password is too short: it must be at least %d characters', 3);

$form->addPassword('password2', 'Reenter password:')
	->setOption('nextTo', 'avatar')
	->addConditionOn($form['password'], Form::VALID)
		->addRule(Form::FILLED, 'Reenter your password')
		->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);

$form->addUpload('avatar', 'Picture:')
	->addCondition(Form::FILLED)
		->addRule(Form::IMAGE, 'Uploaded file is not image');

$form->addHidden('userid');

$form->addTextArea('note', 'Comment:');

$form->addButton('unset');
unset($form['unset']);


$form->addGroup();

$form->addSubmit('submit', 'Send');
$form->addButton('cancel', 'Cancel');


$defaults = [
	'name' => 'John Doe',
	'userid' => 231,
	'country' => 'CZ',
];

$form->setDefaults($defaults);
$form->fireEvents();

Assert::matchFile(__DIR__ . '/Forms.renderer.1.expect', $form->__toString(true));
