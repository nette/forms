<?php

/**
 * Test: Nette\Forms\Controls\DateTimeControl.
 */

declare(strict_types=1);

use Nette\Forms\Controls\DateTimeControl;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('custom date format application', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue('2023-10-22 10:30')
		->setFormat('j.n.Y');

	Assert::same('22.10.2023', $input->getValue());
});


test('timestamp format handling', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue('2023-10-22 10:30')
		->setFormat(DateTimeControl::FormatTimestamp);

	Assert::same(1_697_925_600, $input->getValue());
});


test('dateTime object as value', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue('2023-10-22 10:30')
		->setFormat(DateTimeControl::FormatObject);

	Assert::equal(new DateTimeImmutable('2023-10-22 00:00'), $input->getValue());
});
