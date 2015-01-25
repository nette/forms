<?php

/**
 * Test: Nette\Forms validateMaxPostSize
 */

use Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_LENGTH'] = PHP_INT_MAX;

$form = new Form;
$form->addHidden('x');
$form->isSuccess();

$errors = $form->getErrors();
Assert::count( 1, $errors );
Assert::match( 'The size of the uploaded file can be up to %d% bytes.', $errors[0] );
