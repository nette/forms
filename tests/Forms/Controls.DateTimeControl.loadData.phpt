<?php

/**
 * Test: Nette\Forms\Controls\DateTimeControl.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
	Form::initialize(true);
});


test('not present', function () {
	$form = new Form;
	$input = $form->addDate('unknown');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('invalid data', function () {
	$_POST = ['malformed' => ['']];
	$form = new Form;
	$input = $form->addDate('malformed');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('invalid format', function () {
	$_POST = ['text' => 'invalid'];
	$form = new Form;
	$input = $form->addDate('date');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('invalid date', function () {
	$_POST = ['date' => '2023-13-22'];
	$form = new Form;
	$input = $form->addDate('date');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('invalid time', function () {
	$_POST = ['time' => '10:60'];
	$form = new Form;
	$input = $form->addTime('time');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('empty date', function () {
	$_POST = ['date' => ''];
	$form = new Form;
	$input = $form->addDate('date');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('empty time', function () {
	$_POST = ['time' => ''];
	$form = new Form;
	$input = $form->addTime('time');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('empty date-time', function () {
	$_POST = ['date' => ''];
	$form = new Form;
	$input = $form->addDateTime('date');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('valid date', function () {
	$_POST = ['date' => '2023-10-22'];
	$form = new Form;
	$input = $form->addDate('date');
	Assert::equal(new DateTimeImmutable('2023-10-22 00:00'), $input->getValue());
	Assert::true($input->isFilled());
});


test('valid time', function () {
	$_POST = ['time' => '10:22:33.44'];
	$form = new Form;
	$input = $form->addTime('time');
	Assert::equal(new DateTimeImmutable('0001-01-01 10:22'), $input->getValue());
	Assert::true($input->isFilled());
});


test('valid time with seconds', function () {
	$_POST = ['time' => '10:22:33.44'];
	$form = new Form;
	$input = $form->addTime('time', withSeconds: true);
	Assert::equal(new DateTimeImmutable('0001-01-01 10:22:33'), $input->getValue());
	Assert::true($input->isFilled());
});


test('valid date-time', function () {
	$_POST = ['date' => '2023-10-22T10:23:11.123'];
	$form = new Form;
	$input = $form->addDateTime('date');
	Assert::equal(new DateTimeImmutable('2023-10-22 10:23:00'), $input->getValue());
	Assert::true($input->isFilled());
});


test('valid date-time with seconds', function () {
	$_POST = ['date' => '2023-10-22T10:23:11.123'];
	$form = new Form;
	$input = $form->addDateTime('date', withSeconds: true);
	Assert::equal(new DateTimeImmutable('2023-10-22 10:23:11'), $input->getValue());
	Assert::true($input->isFilled());
});


test('custom date', function () {
	$_POST = ['date' => '22.10.2023'];
	$form = new Form;
	$input = $form->addDate('date');
	Assert::equal(new DateTimeImmutable('2023-10-22 00:00'), $input->getValue());
	Assert::true($input->isFilled());
});
