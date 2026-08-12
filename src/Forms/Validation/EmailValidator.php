<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Utils\Validators;
use function is_scalar;


/**
 * Validates that the value is a syntactically valid email address.
 */
final class EmailValidator implements ValueValidator
{
	public const InvalidSyntax = 'forms.email';


	public function validate(mixed $value): ?Message
	{
		$value = is_scalar($value) || $value instanceof \Stringable ? (string) $value : '';
		return Validators::isEmail($value)
			? null
			: $this->createMessage();
	}


	public function getClientRules(): array
	{
		return [new ClientRule(':email', message: $this->createMessage())];
	}


	private function createMessage(): Message
	{
		return new Message(self::InvalidSyntax, 'Please enter a valid email address.');
	}
}
