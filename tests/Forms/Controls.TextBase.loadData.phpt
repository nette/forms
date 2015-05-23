<?php

/**
 * Test: Nette\Forms\Controls\TextInput.
 */

use Nette\Forms\Form,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function() {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
});


test(function() { // trim & new lines
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$input = $form->addText('text');

	Assert::same( 'a  b   c', $input->getValue() );
	Assert::true( $input->isFilled() );
});


test(function() { // trim & new lines in textarea
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$input = $form->addTextArea('text');

	Assert::same( "  a\n b \n c ", $input->getValue() );
});


test(function() { // empty value
	$_POST = ['url' => 'nette.org'];

	$form = new Form;
	$input = $form->addText('url')
		->setEmptyValue('nette.org');

	Assert::same( '', $input->getValue() );
});


test(function() { // empty value
	$_POST = ['phone' => '+420 '];

	$form = new Form;
	$input = $form->addText('phone')
		->setEmptyValue('+420 ');

	Assert::same( '', $input->getValue() );
});


test(function() { // invalid UTF
	$_POST = ['invalidutf' => "invalid\xAA\xAA\xAAutf"];

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
	$_POST = ['malformed' => [NULL]];

	$form = new Form;
	$input = $form->addText('malformed');

	Assert::same( '', $input->getValue() );
	Assert::false( $input->isFilled() );
});


test(function() { // setValue() and invalid argument
	$_POST = ['text' => "  a\r b \n c "];

	$form = new Form;
	$input = $form->addText('text');
	$input->setValue(NULL);

	Assert::exception(function() use ($input) {
		$input->setValue([]);
	}, 'Nette\InvalidArgumentException', "Value must be scalar or NULL, array given in field 'text'.");
});


test(function() { // float
	$_POST = ['number' => ' 10,5 '];

	$form = new Form;
	$input = $form->addText('number')
		->addRule($form::FLOAT);

	Assert::same( '10,5', $input->getValue() );
	$input->validate();
	Assert::same( 10.5, $input->getValue() );
});



test(function() { // float in condition
	$_POST = ['number' => ' 10,5 '];

	$form = new Form;
	$input = $form->addText('number');
	$input->addCondition($form::FILLED)
			->addRule($form::FLOAT);

	$input->validate();
	Assert::same( 10.5, $input->getValue() );
});


test(function() { // non float
	$_POST = ['number' => ' 10,5 '];

	$form = new Form;
	$input = $form->addText('number')
		->addRule(~$form::FLOAT);

	$input->validate();
	Assert::same( 10.5, $input->getValue() ); // side effect
});


test(function() { // URL
	$_POST = ['url' => 'nette.org'];

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
	$_POST = ['text' => 'hello'];

	$form = new Form;
	$input = $form->addText('text')
		->addFilter('strrev');

	Assert::same( 'hello', $input->getValue() );
	$input->validate();
	Assert::same( 'olleh', $input->getValue() );
});


test(function() { // filter in condition
	$_POST = ['text' => 'hello'];

	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::FILLED)
			->addFilter('strrev');

	Assert::same( 'hello', $input->getValue() );
	$input->validate();
	Assert::same( 'olleh', $input->getValue() );
});
