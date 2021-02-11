<?php

/**
 * Test: FormsExtension.
 */

declare(strict_types=1);

use Nette\Bridges\FormsDI\FormsExtension;
use Nette\DI;
use Nette\Forms\Form;
use Nette\Forms\Validator;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('', function () {
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

	eval($compiler->addConfig($config)->setClassName('Container1')->compile());

	$container = new Container1;
	$container->initialize();
	Assert::same(Validator::$messages[Form::FILLED], 'Testing filled');
	Assert::same(Validator::$messages[Form::EQUAL], 'Testing equal %s.');
	Assert::same(Validator::$messages[Nette\Forms\Controls\SelectBox::VALID], 'SelectBox test');
});


Assert::exception(function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('forms', new FormsExtension);

	$loader = new DI\Config\Loader;
	$config = $loader->load(Tester\FileMock::create('
	forms:
		messages:
			Foo\Bar: custom validator
	', 'neon'));

	eval($compiler->addConfig($config)->setClassName('Container2')->compile());
}, Nette\InvalidArgumentException::class, 'Constant Nette\Forms\Form::Foo\Bar or constant Foo\Bar does not exist.');


test('form factory', function () {
	$compiler = new DI\Compiler;
	$compiler->addExtension('http', new Nette\Bridges\HttpDI\HttpExtension);
	$compiler->addExtension('forms', new FormsExtension);

	eval($compiler->setClassName('Container3')->compile());

	$container = new Container3;
	$container->initialize();
	$factory = $container->getByType(Nette\Forms\FormFactory::class);
	$form = $factory->createForm();
	Assert::type(Form::class, $form);
});
