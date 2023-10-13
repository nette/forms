<?php

/**
 * Nette Forms localization example.
 */

declare(strict_types=1);


if (@!include __DIR__ . '/../vendor/autoload.php') {
	die('Install packages using `composer install`');
}


use Nette\Forms\Form;
use Tracy\Debugger;
use Tracy\Dumper;

Debugger::enable();


class MyTranslator implements Nette\Localization\Translator
{
	private $table;


	public function __construct(array $table)
	{
		$this->table = $table;
	}


	/**
	 * Translates the given string.
	 */
	public function translate($message, ...$parameters): string
	{
		return $this->table[$message] ?? $message;
	}
}


$form = new Form;

$translator = new MyTranslator(parse_ini_file(__DIR__ . '/localization.ini'));
$form->setTranslator($translator);

$form->addGroup('Personal data');
$form->addText('name', 'Your name:')
	->setRequired('Enter your name');

$form->addText('age', 'Your age:')
	->setRequired('Enter your age')
	->addRule($form::Integer, 'Age must be numeric value')
	->addRule($form::Range, 'Age must be in range from %d to %d', [10, 100]);

$countries = [
	'World' => [
		'bu' => 'Buranda',
		'qu' => 'Qumran',
		'st' => 'Saint Georges Island',
	],
	'?' => 'other',
];
$form->addSelect('country', 'Country:', $countries)
	->setPrompt('Select your country');

$form->addSubmit('submit', 'Send');


if ($form->isSuccess()) {
	echo '<h2>Form was submitted and successfully validated</h2>';
	Dumper::dump($form->getValues());
	exit;
}


?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Nette Forms localization example</title>
<link rel="stylesheet" media="screen" href="assets/style.css" />
<script src="https://unpkg.com/nette-forms@3/src/assets/netteForms.js"></script>

<h1>Nette Forms localization example</h1>

<?php $form->render() ?>

<footer><a href="https://doc.nette.org/en/forms">see documentation</a></footer>
