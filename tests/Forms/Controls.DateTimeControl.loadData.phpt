<?php

/**
 * Test: Nette\Forms\Controls\DateTimeControl.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
	ob_start();
	Form::initialize(true);
});


test('unknown date input handling', function () {
	$form = new Form;
	$input = $form->addDate('unknown');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('malformed date input', function () {
	$_POST = ['malformed' => ['']];
	$form = new Form;
	$input = $form->addDate('malformed');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('invalid text date input', function () {
	$_POST = ['text' => 'invalid'];
	$form = new Form;
	$input = $form->addDate('date');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('invalid date string', function () {
	$_POST = ['date' => '2023-13-22'];
	$form = new Form;
	$input = $form->addDate('date');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('invalid time value', function () {
	$_POST = ['time' => '10:60'];
	$form = new Form;
	$input = $form->addTime('time');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('empty date input', function () {
	$_POST = ['date' => ''];
	$form = new Form;
	$input = $form->addDate('date');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('empty time input', function () {
	$_POST = ['time' => ''];
	$form = new Form;
	$input = $form->addTime('time');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('empty datetime input', function () {
	$_POST = ['date' => ''];
	$form = new Form;
	$input = $form->addDateTime('date');
	Assert::null($input->getValue());
	Assert::false($input->isFilled());
});


test('valid date submission', function () {
	$_POST = ['date' => '2023-10-22'];
	$form = new Form;
	$input = $form->addDate('date');
	Assert::equal(new DateTimeImmutable('2023-10-22 00:00'), $input->getValue());
	Assert::true($input->isFilled());
});


test('time without seconds', function () {
	$_POST = ['time' => '10:22:33.44'];
	$form = new Form;
	$input = $form->addTime('time');
	Assert::equal(new DateTimeImmutable('0001-01-01 10:22'), $input->getValue());
	Assert::true($input->isFilled());
});


test('time with seconds', function () {
	$_POST = ['time' => '10:22:33.44'];
	$form = new Form;
	$input = $form->addTime('time', withSeconds: true);
	Assert::equal(new DateTimeImmutable('0001-01-01 10:22:33'), $input->getValue());
	Assert::true($input->isFilled());
});


test('datetime without seconds', function () {
	$_POST = ['date' => '2023-10-22T10:23:11.123'];
	$form = new Form;
	$input = $form->addDateTime('date');
	Assert::equal(new DateTimeImmutable('2023-10-22 10:23:00'), $input->getValue());
	Assert::true($input->isFilled());
});


test('datetime with seconds', function () {
	$_POST = ['date' => '2023-10-22T10:23:11.123'];
	$form = new Form;
	$input = $form->addDateTime('date', withSeconds: true);
	Assert::equal(new DateTimeImmutable('2023-10-22 10:23:11'), $input->getValue());
	Assert::true($input->isFilled());
});


test('alternative date format parsing', function () {
	$_POST = ['date' => '22.10.2023'];
	$form = new Form;
	$input = $form->addDate('date');
	Assert::equal(new DateTimeImmutable('2023-10-22 00:00'), $input->getValue());
	Assert::true($input->isFilled());
});
