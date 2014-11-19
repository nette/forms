<?php

/**
 * Test: FormsExtension.
 */

use Nette\DI,
	Nette\Forms\Validator,
	Nette\Forms\Form,
	Nette\Bridges\FormsDI\FormsExtension,
	Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function() {
	$compiler = new DI\Compiler;
	$compiler->addExtension('forms', new FormsExtension);

	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	forms:
		messages:
			EQUAL: "Testing equal %s."
			FILLED: "Testing filled"
			\'Nette\Forms\Controls\SelectBox::VALID\': "SelectBox test"
	', 'neon'));

	eval($compiler->compile($config, 'Container1'));

	$container = new Container1;
	$container->initialize();
	Assert::same(Validator::$messages[Form::FILLED], 'Testing filled');
	Assert::same(Validator::$messages[Form::EQUAL], 'Testing equal %s.');
	Assert::same(Validator::$messages[Nette\Forms\Controls\SelectBox::VALID], 'SelectBox test');
});


Assert::exception(function() {
	$compiler = new DI\Compiler;
	$compiler->addExtension('forms', new FormsExtension);

	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	forms:
		messages:
			Foo\Bar: custom validator
	', 'neon'));

	eval($compiler->compile($config, 'Container2'));
}, 'Nette\InvalidArgumentException', 'Constant Nette\Forms\Form::Foo\Bar or constant Foo\Bar does not exist.');
