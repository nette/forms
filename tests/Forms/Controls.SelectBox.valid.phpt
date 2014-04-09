<?php

/**
 * Test: Nette\Forms\Controls\CsrfProtection.
 *
 * @author     David Grudl
 */

use Nette\Forms\Controls\SelectBox,
	Nette\Forms\Validator,
	Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';


$form = new Form;
Validator::$messages[SelectBox::VALID] = 'SelectBox "%label" must be filled.';
$input = $form->addSelect('foo', 'Foo', array('bar' => 'Bar'));

$form->fireEvents();

Assert::same( array('SelectBox "Foo" must be filled.'), $form->getErrors() );


$input->setValue(NULL);
Assert::false(SelectBox::validateValid($input));

$input->setDisabled(TRUE);
Assert::true(SelectBox::validateValid($input));

$input->setPrompt('Empty');
Assert::true(SelectBox::validateValid($input));

$input->setDisabled(FALSE);
Assert::true(SelectBox::validateValid($input));

$input->setValue('bar');
Assert::true(SelectBox::validateValid($input));

$input->setDisabled(TRUE);
Assert::true(SelectBox::validateValid($input));

$input->setPrompt(FALSE);
Assert::true(SelectBox::validateValid($input));
