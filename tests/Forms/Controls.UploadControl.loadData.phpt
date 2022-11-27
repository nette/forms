<?php

/**
 * Test: Nette\Forms\Controls\UploadControl.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Validator;
use Nette\Http\FileUpload;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';
$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';

$_FILES = [
	'avatar' => [
		'name' => 'license.txt',
		'type' => 'text/plain',
		'tmp_name' => __DIR__ . '/files/logo.gif',
		'error' => 0,
		'size' => 3013,
	],
	'container' => [
		'name' => ['avatar' => "invalid\xAA\xAA\xAAutf"],
		'type' => ['avatar' => 'text/plain'],
		'tmp_name' => ['avatar' => 'C:\\PHP\\temp\\php1D5C.tmp'],
		'error' => ['avatar' => 0],
		'size' => ['avatar' => 3013],
	],
	'multiple' => [
		'name' => ['avatar' => ['image.gif', 'image.png']],
		'type' => ['avatar' => ['a', 'b']],
		'tmp_name' => ['avatar' => [__DIR__ . '/files/logo.gif', __DIR__ . '/files/logo.gif']],
		'error' => ['avatar' => [0, 0]],
		'size' => ['avatar' => [100, 200]],
	],
	'empty' => [
		'name' => [''],
		'type' => [''],
		'tmp_name' => [''],
		'error' => [UPLOAD_ERR_NO_FILE],
		'size' => [0],
	],
	'invalid1' => [
		'name' => [null],
		'type' => [null],
		'tmp_name' => [null],
		'error' => [null],
		'size' => [null],
	],
	'invalid2' => '',
	'partial' => [
		'name' => 'license.txt',
		'type' => 'text/plain',
		'tmp_name' => __DIR__ . '/files/logo.gif',
		'error' => UPLOAD_ERR_PARTIAL,
		'size' => 3013,
	],
];


test('', function () {
	$form = new Form;
	$input = $form->addUpload('avatar');

	Assert::true($form->isValid());
	Assert::equal(new FileUpload([
		'name' => 'license.txt',
		'type' => '',
		'size' => 3013,
		'tmp_name' => __DIR__ . '/files/logo.gif',
		'error' => 0,
	]), $input->getValue());
	Assert::true($input->isFilled());
	Assert::true($input->isOk());
});


test('container', function () {
	$form = new Form;
	$input = $form->addContainer('container')->addUpload('avatar');

	Assert::true($form->isValid());
	Assert::equal(new FileUpload([
		'name' => '',
		'type' => '',
		'size' => 3013,
		'tmp_name' => 'C:\\PHP\\temp\\php1D5C.tmp',
		'error' => 0,
	]), $input->getValue());
	Assert::true($input->isFilled());
	Assert::true($input->isOk());
});


test('multiple (in container)', function () {
	$form = new Form;
	$input = $form->addContainer('multiple')->addMultiUpload('avatar');

	Assert::true($form->isValid());
	Assert::equal([new FileUpload([
		'name' => 'image.gif',
		'type' => '',
		'size' => 100,
		'tmp_name' => __DIR__ . '/files/logo.gif',
		'error' => 0,
	]), new FileUpload([
		'name' => 'image.png',
		'type' => '',
		'size' => 200,
		'tmp_name' => __DIR__ . '/files/logo.gif',
		'error' => 0,
	])], $input->getValue());
	Assert::true($input->isFilled());
	Assert::true($input->isOk());
});


test('missing data', function () {
	$form = new Form;
	$input = $form->addMultiUpload('empty')
		->setRequired();

	Assert::false($form->isValid());
	Assert::equal([], $input->getValue());
	Assert::false($input->isFilled());
	Assert::false($input->isOk());
});


test('empty data', function () {
	$form = new Form;
	$input = $form->addUpload('missing')
		->setRequired();

	Assert::false($form->isValid());
	Assert::equal(new FileUpload([]), $input->getValue());
	Assert::false($input->isFilled());
	Assert::false($input->isOk());
});


test('malformed data', function () {
	$form = new Form;
	$input = $form->addUpload('invalid1');

	Assert::true($form->isValid());
	Assert::equal(new FileUpload([]), $input->getValue());
	Assert::false($input->isFilled());
	Assert::false($input->isOk());

	$form = new Form;
	$input = $form->addUpload('invalid2');

	Assert::true($form->isValid());
	Assert::equal(new FileUpload([]), $input->getValue());
	Assert::false($input->isFilled());
	Assert::false($input->isOk());

	$form = new Form;
	$input = $form->addMultiUpload('avatar');

	Assert::true($form->isValid());
	Assert::equal([], $input->getValue());
	Assert::false($input->isFilled());
	Assert::false($input->isOk());

	$form = new Form;
	$input = $form->addContainer('multiple')->addUpload('avatar');

	Assert::true($form->isValid());
	Assert::equal(new FileUpload([]), $input->getValue());
	Assert::false($input->isFilled());
	Assert::false($input->isOk());
});


test('partial uploaded (error)', function () {
	$form = new Form;
	$input = $form->addUpload('partial')
		->setRequired();

	Assert::false($form->isValid());
	Assert::equal(new FileUpload([
		'name' => 'license.txt',
		'type' => '',
		'tmp_name' => __DIR__ . '/files/logo.gif',
		'error' => UPLOAD_ERR_PARTIAL,
		'size' => 3013,
	]), $input->getValue());
	Assert::true($input->isFilled());
	Assert::false($input->isOk());
});


test('validators', function () {
	$form = new Form;
	$input = $form->addUpload('avatar')
		->addRule($form::MaxFileSize, null, 3000);

	Assert::false(Validator::validateFileSize($input, 3012));
	Assert::true(Validator::validateFileSize($input, 3013));

	Assert::true(Validator::validateMimeType($input, 'image/gif'));
	Assert::true(Validator::validateMimeType($input, 'image/*'));
	Assert::false(Validator::validateMimeType($input, 'text/*'));
	Assert::true(Validator::validateMimeType($input, 'text/css,image/*'));
	Assert::true(Validator::validateMimeType($input, ['text/css', 'image/*']));
	Assert::false(Validator::validateMimeType($input, []));

	Assert::true(Validator::validateImage($input));
});


test('validators on multiple files', function () {
	$form = new Form;
	$input = $form->addContainer('multiple')->addMultiUpload('avatar')
		->addRule($form::MaxFileSize, null, 3000);

	Assert::false(Validator::validateFileSize($input, 150));
	Assert::true(Validator::validateFileSize($input, 300));

	Assert::true(Validator::validateMimeType($input, 'image/gif'));
	Assert::true(Validator::validateMimeType($input, 'image/*'));
	Assert::false(Validator::validateMimeType($input, 'text/*'));
	Assert::true(Validator::validateMimeType($input, 'text/css,image/*'));
	Assert::true(Validator::validateMimeType($input, ['text/css', 'image/*']));
	Assert::false(Validator::validateMimeType($input, []));

	Assert::true(Validator::validateImage($input));
});


test('validators on multiple files', function () {
	$form = new Form;
	$input = $form->addUpload('invalid1');

	$rules = iterator_to_array($input->getRules());
	Assert::count(2, $rules);
	Assert::same($form::MaxFileSize, $rules[1]->validator);
});
