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
$_POST = ['name' => 'John Doe ', 'age' => '9.9', 'email' => '@', 'street' => '', 'city' => 'Troubsko', 'country' => '0', 'password' => 'xx', 'password2' => 'xx', 'note' => '', 'submit1' => 'Send', 'userid' => '231'];


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
	'm' => Html::el('option', 'male')->style('color: #248bd3'),
	'f' => Html::el('option', 'female')->style('color: #e948d4'),
];


$form = new Form;

$renderer = $form->renderer;
$renderer->wrappers['form']['container'] = Html::el('div')->id('form');
$renderer->wrappers['form']['errors'] = false;
$renderer->wrappers['group']['container'] = null;
$renderer->wrappers['group']['label'] = 'h3';
$renderer->wrappers['pair']['container'] = null;
$renderer->wrappers['controls']['container'] = 'dl';
$renderer->wrappers['control']['container'] = 'dd';
$renderer->wrappers['control']['.odd'] = 'odd';
$renderer->wrappers['control']['.multi'] = 'multi';
$renderer->wrappers['control']['.error'] = 'is-invalid';
$renderer->wrappers['control']['errors'] = true;
$renderer->wrappers['label']['container'] = 'dt';
$renderer->wrappers['label']['suffix'] = ':';
$renderer->wrappers['control']['requiredsuffix'] = " \u{2022}";


$form->addGroup('Personal data');
$form->addText('name', 'Your name')
	->addRule(Form::FILLED, 'Enter your name')
	->setOption('class', 'myclass')
	->setOption('id', 'myid');

$form->addText('age', 'Your age')
	->addRule(Form::FILLED, 'Enter your age')
	->addRule(Form::INTEGER, 'Age must be numeric value')
	->addRule(Form::RANGE, 'Age must be in range from %d to %d', [10, 100]);

$form->addSelect('gender', 'Your gender', $sex);

$form->addText('email', 'Email')
	->setEmptyValue('@')
	->addCondition(Form::FILLED)
		->addRule(Form::EMAIL, 'Incorrect email address');


$form->addGroup('Shipping address')
	->setOption('embedNext', true);

$form->addCheckbox('send', 'Ship to address')
	->addCondition(Form::EQUAL, true)
		->toggle('sendBox');


$form->addGroup()
	->setOption('container', Html::el('div')->id('sendBox'));

$form->addText('street', 'Street');

$form->addText('city', 'City')
	->addConditionOn($form['send'], Form::EQUAL, true)
		->addRule(Form::FILLED, 'Enter your shipping address');

$form->addSelect('country', 'Country', $countries)
	->setPrompt('Select your country')
	->addConditionOn($form['send'], Form::EQUAL, true)
		->addRule(Form::FILLED, 'Select your country');


$form->addGroup('Your account');

$form->addPassword('password', 'Choose password')
	->setOption('nextTo', 'password2')
	->addRule(Form::FILLED, 'Choose your password')
	->addRule(Form::MIN_LENGTH, 'The password is too short: it must be at least %d characters', 3)
	->setOption('description', '(at least 3 characters)');

$form->addPassword('password2', 'Reenter password')
	->addConditionOn($form['password'], Form::VALID)
		->addRule(Form::FILLED, 'Reenter your password')
		->addRule(Form::EQUAL, 'Passwords do not match', $form['password']);

$form->addUpload('avatar', 'Picture');

$form->addHidden('userid');

$form->addTextArea('note', 'Comment');


$form->addGroup();

$form->addSubmit('submit', 'Send');


$defaults = [
	'name' => 'John Doe',
	'userid' => 231,
	'country' => 'CZ',
];

$form->setDefaults($defaults);
$form->fireEvents();

Assert::matchFile(__DIR__ . '/Forms.renderer.2.expect', $form->__toString(true));
