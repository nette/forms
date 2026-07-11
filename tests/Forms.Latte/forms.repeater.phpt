<?php declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\{Container, Form};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Form;
$persons = $form->addRepeater('persons', function (Container $person) {
	$person->addText('name', 'Name');
	$person->addRepeater('emails', function (Container $email) {
		$email->addText('email');
	});
});
$persons->setBounds(min: 1, max: 3);
$form->addSubmit('send', 'Send');


$latte = new Latte\Engine;
$latte->addExtension(new FormsExtension);
$latte->addProvider('uiControl', ['myForm' => $form]);

Assert::matchFile(
	__DIR__ . '/expected/forms.repeater.html',
	$latte->renderToString(__DIR__ . '/templates/forms.repeater.latte'),
);
