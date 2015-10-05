<?php

/**
 * Nette Forms localization example.
 */


if (@!include __DIR__ . '/../vendor/autoload.php') {
	die('Install packages using `composer install`');
}


use Nette\Forms\Form;
use Tracy\Debugger;
use Tracy\Dumper;

Debugger::enable();


class MyTranslator implements Nette\Localization\ITranslator
{
	private $table;

	function __construct(array $table)
	{
		$this->table = $table;
	}

	/**
	 * Translates the given string.
	 */
	public function translate($message, $count = NULL)
	{
		return isset($this->table[$message]) ? $this->table[$message] : $message;
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
	->addRule($form::INTEGER, 'Age must be numeric value')
	->addRule($form::RANGE, 'Age must be in range from %d to %d', array(10, 100));

$countries = array(
	'World' => array(
		'bu' => 'Buranda',
		'qu' => 'Qumran',
		'st' => 'Saint Georges Island',
	),
	'?' => 'other',
);
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
<script src="https://nette.github.io/resources/js/netteForms.js"></script>

<h1>Nette Forms localization example</h1>

<?php echo $form ?>

<footer><a href="https://doc.nette.org/en/forms">see documentation</a></footer>
