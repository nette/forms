<?php

/**
 * Test: Nette\Forms\Controls\Button.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class Translator implements Nette\Localization\ITranslator
{
	public function translate($s, ...$parameters): string
	{
		return strtoupper($s);
	}
}


test('basic image button rendering', function () {
	$form = new Form;
	$input = $form->addImageButton('button', 'image.gif');

	Assert::null($input->getLabel());
	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="image" name="button[]" src="image.gif">', (string) $input->getControl());
});


test('translator does not affect image button', function () {
	$form = new Form;
	$input = $form->addImageButton('button', 'image.gif');
	$input->setTranslator(new Translator);

	Assert::same('<input type="image" name="button[]" src="image.gif">', (string) $input->getControl());
});


test('required image button attributes', function () {
	$form = new Form;
	$input = $form->addImageButton('button', 'image.gif')->setRequired('required');

	Assert::same('<input type="image" name="button[]" src="image.gif">', (string) $input->getControl());
});


test('image button within container', function () {
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addImageButton('button', 'image.gif');

	Assert::same('<input type="image" name="container[button][]" src="image.gif">', (string) $input->getControl());
});


test('image button options after rendering', function () {
	$form = new Form;
	$input = $form->addImageButton('button');

	Assert::same('button', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});
