<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Controls\BaseControl::setFixedValue()
 */

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('fixed value is rendered disabled and kept among values', function () {
	$form = new Form;
	$input = $form->addText('id')->setFixedValue('123');

	Assert::true($input->isDisabled());
	Assert::same('123', $input->getValue());
	Assert::same(['id' => '123'], $form->getValues('array'));
	Assert::contains('disabled', (string) $input->getControl());
});


test('submitted value is ignored', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = ['id' => 'tampered', 'name' => 'John'];

	$form = new Form;
	$form->allowCrossOrigin();
	$form->addText('id')->setFixedValue('123');
	$form->addText('name');

	Assert::truthy($form->isSubmitted());
	Assert::same(['id' => '123', 'name' => 'John'], $form->getValues('array'));
});
