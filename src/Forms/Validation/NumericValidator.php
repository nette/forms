<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use function is_int, is_string;


/**
 * Validates that the value is a non-negative integer written in digits only.
 */
final class NumericValidator implements ValueValidator
{
	public const NotNumeric = 'forms.numeric';


	public function validate(mixed $value): ?Message
	{
		return (is_int($value) && $value >= 0) || (is_string($value) && preg_match('#^\d+$#D', $value))
			? null
			: $this->createMessage();
	}


	public function getClientRule(): ClientRule
	{
		return new ClientRule(':numeric', message: $this->createMessage());
	}


	private function createMessage(): Message
	{
		return new Message(self::NotNumeric, 'Please enter a non-negative integer.');
	}
}
