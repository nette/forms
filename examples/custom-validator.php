<?php

/**
 * Nette Forms custom validator example.
 */

declare(strict_types=1);


if (@!include __DIR__ . '/../vendor/autoload.php') {
	die('Install packages using `composer install`');
}

use Nette\Forms\Form;
use Tracy\Debugger;
use Tracy\Dumper;

Debugger::enable();


// Define custom validator
class MyValidators
{
	public static function divisibilityValidator($item, $arg): bool
	{
		return $item->value % $arg === 0;
	}
}


$form = new Form;

$form->addText('num1', 'Multiple of 8:')
	->setDefaultValue(5)
	->addRule('MyValidators::divisibilityValidator', 'First number must be %d multiple', 8);

$form->addSubmit('submit', 'Send');


if ($form->isSuccess()) {
	echo '<h2>Form was submitted and successfully validated</h2>';
	Dumper::dump($form->getValues());
	exit;
}


?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Nette Forms custom validator example</title>
<link rel="stylesheet" media="screen" href="assets/style.css" />
<script src="https://unpkg.com/nette-forms@3"></script>

<script>
	Nette.validators.MyValidators_divisibilityValidator = function(elem, args, val) {
		return val % args === 0;
	};
</script>

<h1>Nette Forms custom validator example</h1>

<?php $form->render() ?>

<footer><a href="https://doc.nette.org/en/forms">see documentation</a></footer>
