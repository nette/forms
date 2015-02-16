<?php

/**
 * Test: Nette\Forms\Controls\TextInput.
 */

use Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function() {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = array();
});


test(function() { // trim & new lines
	$_POST = array('text' => "  a\r b \n c ");

	$form = new Form;
	$input = $form->addText('text');

	Assert::same( 'a  b   c', $input->getValue() );
	Assert::true( $input->isFilled() );
});


test(function() { // trim & new lines in textarea
	$_POST = array('text' => "  a\r b \n c ");

	$form = new Form;
	$input = $form->addTextArea('text');

	Assert::same( "  a\n b \n c ", $input->getValue() );
});


test(function() { // empty value
	$_POST = array('url' => 'nette.org');

	$form = new Form;
	$input = $form->addText('url')
		->setEmptyValue('nette.org');

	Assert::same( '', $input->getValue() );
});


test(function() { // empty value
	$_POST = array('phone' => '+420 ');

	$form = new Form;
	$input = $form->addText('phone')
		->setEmptyValue('+420 ');

	Assert::same( '', $input->getValue() );
});


test(function() { // invalid UTF
	$_POST = array('invalidutf' => "invalid\xAA\xAA\xAAutf");

	$form = new Form;
	$input = $form->addText('invalidutf');
	Assert::same( '', $input->getValue() );
});


test(function() { // missing data
	$form = new Form;
	$input = $form->addText('unknown');

	Assert::same( '', $input->getValue() );
	Assert::false( $input->isFilled() );
});


test(function() { // malformed data
	$_POST = array('malformed' => array(NULL));

	$form = new Form;
	$input = $form->addText('malformed');

	Assert::same( '', $input->getValue() );
	Assert::false( $input->isFilled() );
});


test(function() { // setValue() and invalid argument
	$_POST = array('text' => "  a\r b \n c ");

	$form = new Form;
	$input = $form->addText('text');
	$input->setValue(NULL);

	Assert::exception(function() use ($input) {
		$input->setValue(array());
	}, 'Nette\InvalidArgumentException', "Value must be scalar or NULL, array given in field 'text'.");
});


test(function() { // float
	$_POST = array('number' => ' 10,5 ');

	$form = new Form;
	$input = $form->addText('number')
		->addRule($form::FLOAT);

	Assert::same( '10,5', $input->getValue() );
	$input->validate();
	Assert::same( 10.5, $input->getValue() );
});



test(function() { // float in condition
	$_POST = array('number' => ' 10,5 ');

	$form = new Form;
	$input = $form->addText('number');
	$input->addCondition($form::FILLED)
			->addRule($form::FLOAT);

	$input->validate();
	Assert::same( 10.5, $input->getValue() );
});


test(function() { // non float
	$_POST = array('number' => ' 10,5 ');

	$form = new Form;
	$input = $form->addText('number')
		->addRule(~$form::FLOAT);

	$input->validate();
	Assert::same( 10.5, $input->getValue() ); // side effect
});


test(function() { // URL
	$_POST = array('url' => 'nette.org');

	$form = new Form;
	$input = $form->addText('url')
		->addRule($form::URL);

	$input->validate();
	Assert::same( 'http://nette.org', $input->getValue() );
});


test(function() { // object
	$form = new Form;
	$input = $form->addText('text')
		->setValue($date = new Nette\Utils\DateTime('2013-07-05'));

	Assert::same( $date, $input->getValue() );
});


test(function() { // filter
	$_POST = array('text' => 'hello');

	$form = new Form;
	$input = $form->addText('text')
		->addFilter('strrev');

	Assert::same( 'hello', $input->getValue() );
	$input->validate();
	Assert::same( 'olleh', $input->getValue() );
});
