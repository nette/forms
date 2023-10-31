<?php

/**
 * Test: Nette\Forms\Controls\ColorPicker.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('', function () {
	$form = new Form;
	$input = $form->addColor('color')
		->setValue('1020AB');

	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="color" name="color" id="frm-color" value="#1020ab">', (string) $input->getControl());
});
