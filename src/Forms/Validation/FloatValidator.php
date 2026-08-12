<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Utils\Validators;
use function is_string;


/**
 * Validates that the value is a number, tolerating spaces and a decimal comma,
 * and normalizes it to the float type.
 */
final class FloatValidator implements ValueValidator, Filter
{
	public const NotNumber = 'forms.float';


	public function validate(mixed $value): ?Message
	{
		return Validators::isNumeric(self::normalize($value))
			? null
			: $this->createMessage();
	}


	public function filter(mixed $value): mixed
	{
		return (float) self::normalize($value);
	}


	private static function normalize(mixed $value): mixed
	{
		return is_string($value)
			? str_replace([' ', ','], ['', '.'], $value)
			: $value;
	}


	public function getClientRules(): array
	{
		// the client-side counterpart normalizes the value the same way
		return [new ClientRule(':float', message: $this->createMessage())];
	}


	private function createMessage(): Message
	{
		return new Message(self::NotNumber, 'Please enter a valid number.');
	}
}
