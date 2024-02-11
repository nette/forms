<?php

/**
 * Test: Nette\Forms\Controls\BaseControl
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Validator;
use Nette\Http\FileUpload;
use Nette\Utils\AssertionException;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class CustomControl implements \Nette\Forms\IControl
{

	private $value;


	public function __construct($value)
	{
		$this->value = $value;
	}


	public function setValue($value)
	{
		$this->value = $value;
	}


	public function getValue()
	{
		return $this->value;
	}


	public function validate(): void
	{
	}


	public function getErrors(): array
	{
		return [];
	}


	public function isOmitted(): bool
	{
		return false;
	}

}


test(function () { // filled, blank
	$input = new CustomControl('');
	Assert::false(Validator::validateFilled($input));
	Assert::true(Validator::validateBlank($input));

	$input = new CustomControl(null);
	Assert::false(Validator::validateFilled($input));
	Assert::true(Validator::validateBlank($input));

	$input = new CustomControl([]);
	Assert::false(Validator::validateFilled($input));
	Assert::true(Validator::validateBlank($input));

	$input = new CustomControl(42);
	Assert::true(Validator::validateFilled($input));
	Assert::false(Validator::validateBlank($input));
});


test(function () { // file upload related validators
	$input = new CustomControl(new FileUpload([
		'name' => 'foo',
		'size' => 1,
		'tmp_name' => __FILE__,
		'error' => UPLOAD_ERR_OK
	]));
	Assert::true(Validator::validateFileSize($input, 42));
	Assert::true(Validator::validateMimeType($input, ['text/x-php']));
	Assert::false(Validator::validateImage($input));

	$input = new CustomControl(new FileUpload([
		'name' => 'foo',
		'size' => 100,
		'tmp_name' => __DIR__ . '/files/logo.gif',
		'error' => UPLOAD_ERR_OK
	]));
	Assert::false(Validator::validateFileSize($input, 42));
	Assert::false(Validator::validateMimeType($input, ['text/x-php']));
	Assert::true(Validator::validateImage($input));

	Assert::exception(
		function () : void {
			Assert::false(Validator::validateFileSize(new CustomControl('foo'), 42));
		},
		AssertionException::class
	);

	Assert::exception(
		function () : void {
			Assert::false(Validator::validateMimeType(new CustomControl('foo'), ['plain/text']));
		},
		AssertionException::class
	);

	Assert::exception(
		function () : void {
			Assert::false(Validator::validateImage(new CustomControl('foo')));
		},
		AssertionException::class
	);
});
