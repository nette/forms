<?php

/**
 * Test: Nette\Forms\Controls\DateTimeControl.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Validator;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


testException('invalid value type exception', function () {
	$form = new Form;
	$input = $form->addDate('date');
	$input->setValue([]);
}, TypeError::class, 'Value must be DateTimeInterface|string|int|null, array given.');


test('empty string as null', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue('');

	Assert::null($input->getValue());
});


test('date string parsing', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue('2013-07-05 10:30');

	Assert::equal(new DateTimeImmutable('2013-07-05 00:00'), $input->getValue());
});


testException('invalid date exception', function () {
	$form = new Form;
	$input = $form->addDate('date');
	$input->setValue('2023-02-31');
}, Nette\InvalidArgumentException::class, "The parsed date was invalid '2023-02-31'");


test('timestamp string parsing', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue('254400000');

	Assert::equal(new DateTimeImmutable('1978-01-23 00:00'), $input->getValue());
});


test('integer timestamp parsing', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue(254_400_000);

	Assert::equal(new DateTimeImmutable('1978-01-23 00:00'), $input->getValue());
});


test('dateTime object parsing', function () {
	$form = new Form;
	$input = $form->addDate('date')
		->setValue(new Nette\Utils\DateTime('2023-10-05 11:22:33.44'));

	Assert::equal(new DateTimeImmutable('2023-10-05 00:00'), $input->getValue());
});


test('time extraction from DateTime', function () {
	$form = new Form;
	$input = $form->addTime('time')
		->setValue(new Nette\Utils\DateTime('2023-10-05 11:22:33.44'));

	Assert::equal(new DateTimeImmutable('0001-01-01 11:22'), $input->getValue());
});


test('datetime value extraction', function () {
	$form = new Form;
	$input = $form->addDateTime('time')
		->setValue(new Nette\Utils\DateTime('2023-10-05 11:22:33.44'));

	Assert::equal(new DateTimeImmutable('2023-10-05 11:22'), $input->getValue());
});


test('datetime with seconds extraction', function () {
	$form = new Form;
	$input = $form->addDateTime('time', withSeconds: true)
		->setValue(new Nette\Utils\DateTime('2023-10-05 11:22:33.44'));

	Assert::equal(new DateTimeImmutable('2023-10-05 11:22:33'), $input->getValue());
});


test('datetime range validation', function () {
	$form = new Form;
	$input = $form->addDateTime('time', null, true)
		->setValue(new DateTime('2023-10-05'));

	Assert::true(Validator::validateRange($input, [new DateTime('2023-09-05'), new DateTime('2023-11-05')]));
	Assert::false(Validator::validateRange($input, [new DateTime('2023-11-05'), new DateTime('2023-09-05')]));
	Assert::true(Validator::validateRange($input, ['2023-09-05', '2023-11-05']));
	Assert::false(Validator::validateRange($input, ['2023-11-05', '2023-09-05']));
});


test('time range validation', function () {
	$form = new Form;
	$input = $form->addTime('time', null, true)
		->setValue(new DateTime('12:30'));

	Assert::true(Validator::validateRange($input, [new DateTime('12:30'), new DateTime('14:00')]));
	Assert::false(Validator::validateRange($input, [new DateTime('13:00'), new DateTime('14:00')]));
	Assert::true(Validator::validateRange($input, ['12:30', '14:00']));
	Assert::false(Validator::validateRange($input, ['13:00', '14:00']));

	Assert::true(Validator::validateRange($input, ['12:30']));
	Assert::true(Validator::validateRange($input, [null, '12:30']));

	// cross midnight
	Assert::true(Validator::validateRange($input, ['21:00', '13:00']));
	Assert::false(Validator::validateRange($input, ['21:00', '12:00']));
});
