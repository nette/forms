<?php

/**
 * Test: FormsExtension.
 */

use Nette\DI,
	Nette\Bridges\FormsDI\FormsExtension,
	Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/include.php';



$compiler = new DI\Compiler;
$compiler->addExtension('nette', new FooExtension);
$compiler->addExtension('forms', new FormsExtension);

$container1 = createContainer($compiler, '
forms:
	messages:
		EQUAL: "Testing equal %s."
		FILLED: "Testing filled"
		\'Nette\Forms\Controls\SelectBox::VALID\': "SelectBox test"
');
$container1->initialize();
Assert::same(Nette\Forms\Validator::$messages[Nette\Forms\Form::FILLED], 'Testing filled');
Assert::same(Nette\Forms\Validator::$messages[Nette\Forms\Form::EQUAL], 'Testing equal %s.');
Assert::same(Nette\Forms\Validator::$messages[Nette\Forms\Controls\SelectBox::VALID], 'SelectBox test');

Assert::exception(function() use ($compiler) {
		createContainer($compiler, '
			forms:
				messages:
					Foo\Bar: custom validator
		');
	}, 'Nette\InvalidArgumentException', 'Constant Nette\Forms\Form::Foo\Bar or constant Foo\Bar does not exist.');


// back compatibility
$container2 = createContainer($compiler, '
nette:
	forms:
		messages:
			EMAIL: "Testing email"
');
$container2->initialize();
Assert::same(Nette\Forms\Validator::$messages[Nette\Forms\Form::EMAIL], 'Testing email');
