<?php

/**
 * Test: Nette\Forms\Controls\DateTimeControl.
 */

declare(strict_types=1);

use Nette\Forms\Controls\DateTimeControl;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('string format', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue('2023-10-22 10:30')
		->setFormat('j.n.Y');

	Assert::same('22.10.2023', $input->getValue());
});


test('timestamp format', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue('2023-10-22 10:30')
		->setFormat(DateTimeControl::FormatTimestamp);

	Assert::same(1697925600, $input->getValue());
});


test('object format', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue('2023-10-22 10:30')
		->setFormat(DateTimeControl::FormatObject);

	Assert::equal(new DateTimeImmutable('2023-10-22 00:00'), $input->getValue());
});
