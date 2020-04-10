<?php

/**
 * Test: Nette\Forms\Controls\BaseControl
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Validator;
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
