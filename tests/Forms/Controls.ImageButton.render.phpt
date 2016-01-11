<?php

/**
 * Test: Nette\Forms\Controls\Button.
 */

use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Translator implements Nette\Localization\ITranslator
{
	function translate($s, $plural = NULL)
	{
		return strtoupper($s);
	}
}


test(function () {
	$form = new Form;
	$input = $form->addImage('button', 'image.gif');

	Assert::null($input->getLabel());
	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="image" name="button[]" src="image.gif">', (string) $input->getControl());
});


test(function () { // translator
	$form = new Form;
	$input = $form->addImage('button', 'image.gif');
	$input->setTranslator(new Translator);

	Assert::same('<input type="image" name="button[]" src="image.gif">', (string) $input->getControl());
});


test(function () { // no validation rules
	$form = new Form;
	$input = $form->addImage('button', 'image.gif')->setRequired('required');

	Assert::same('<input type="image" name="button[]" src="image.gif">', (string) $input->getControl());
});


test(function () { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addImage('button', 'image.gif');

	Assert::same('<input type="image" name="container[button][]" src="image.gif">', (string) $input->getControl());
});


test(function () { // rendering options
	$form = new Form;
	$input = $form->addImage('button');

	Assert::same('button', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});
