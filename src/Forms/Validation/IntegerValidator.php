<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Utils\Validators;
use function is_float;


/**
 * Validates that the value is an integer and normalizes it to the int type.
 */
final class IntegerValidator implements ValueValidator, Filter
{
	public const NotInteger = 'forms.integer';


	public function validate(mixed $value): ?Message
	{
		return Validators::isNumericInt($value) && !is_float($value * 1) // too big for int?
			? null
			: $this->createMessage();
	}


	public function filter(mixed $value): mixed
	{
		return $value * 1;
	}


	public function getClientRules(): array
	{
		// the client-side counterpart normalizes the value the same way
		return [new ClientRule(':integer', message: $this->createMessage())];
	}


	private function createMessage(): Message
	{
		return new Message(self::NotInteger, 'Please enter a valid integer.');
	}
}
