<?php

/**
 * Nette Forms live validation example.
 */

declare(strict_types=1);


if (@!include __DIR__ . '/../vendor/autoload.php') {
	die('Install packages using `composer install`');
}

use Nette\Forms\Form;
use Tracy\Debugger;
use Tracy\Dumper;

Debugger::enable();


$form = new Form;
$form->addText('name', 'Your name:')
	->setRequired('Enter your name');

$form->addText('age', 'Your age:')
	->setRequired('Enter your age')
	->addRule($form::Integer, 'Age must be numeric value')
	->addRule($form::Range, 'Age must be in range from %d to %d', [10, 100]);

$form->addPassword('password', 'Choose password:')
	->setRequired('Choose your password')
	->addRule($form::MinLength, 'The password is too short: it must be at least %d characters', 3);

$form->addPassword('password2', 'Reenter password:')
	->setRequired('Reenter your password')
	->addRule($form::Equal, 'Passwords do not match', $form['password']);

$form->addSubmit('submit', 'Send');


if ($form->isSuccess()) {
	echo '<h2>Form was submitted and successfully validated</h2>';
	Dumper::dump($form->getValues());
	exit;
}

$renderer = $form->getRenderer();
$renderer->wrappers['pair']['.error'] = 'has-error';

?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Nette Forms live validation example</title>
<link rel="stylesheet" media="screen" href="assets/style.css" />
<script src="https://unpkg.com/nette-forms@3"></script>
<script src="https://code.jquery.com/jquery-3.0.0.min.js" integrity="sha256-JmvOoLtYsmqlsWxa7mDSLMwa6dZ9rrIdtrrVYRnDRH0=" crossorigin="anonymous"></script>

<script>
function showErrors(errors, focus)
{
	errors.forEach(function(error) {
		if (error.message) {
			$(error.element).closest('tr').addClass('has-error').find('.error').remove();
			$('<span class=error>').text(error.message).insertAfter(error.element);
		}

		if (focus && error.element.focus) {
			error.element.focus();
			focus = false;
		}
	});
}

function removeErrors(elem)
{
	if ($(elem).is('form')) {
		$('.has-error', elem).removeClass('has-error');
		$('.error', elem).remove();
	} else {
		$(elem).closest('tr').removeClass('has-error').find('.error').remove();
	}
}

Nette.showFormErrors = function(form, errors) {
	removeErrors(form);
	showErrors(errors, true);
};

$(function() {
	$(':input').keypress(function() {
		removeErrors(this);
	});

	$(':input').blur(function() {
		Nette.formErrors = [];
		Nette.validateControl(this);
		showErrors(Nette.formErrors);
	});
});
</script>

<h1>Nette Forms live validation example</h1>

<?php $form->render() ?>

<footer><a href="https://doc.nette.org/en/forms">see documentation</a></footer>
