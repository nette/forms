<?php

/**
 * Test: Nette\Forms validateMaxPostSize
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_LENGTH'] = PHP_INT_MAX;
$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';

$form = new Form;
$form->addHidden('x');
$form->isSuccess();

$errors = $form->getErrors();
Assert::count(1, $errors);
Assert::match('The size of the uploaded file can be up to %d% bytes.', $errors[0]);
