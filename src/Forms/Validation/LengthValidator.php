<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Utils\Strings;
use function is_int, is_scalar;


/**
 * Validates the length of a string in characters.
 */
final readonly class LengthValidator implements ValueValidator
{
	use CountBounds;

	public const
		TooShort = 'forms.length.min',
		TooLong = 'forms.length.max',
		NotExact = 'forms.length.exact',
		NotInRange = 'forms.length';


	public function validate(mixed $value): ?Message
	{
		$value = is_scalar($value) || $value instanceof \Stringable ? (string) $value : '';
		return $this->checkCount(Strings::length($value));
	}


	public function getClientRule(): ClientRule
	{
		return $this->createClientRule(is_int($this->max) ? ['maxlength' => $this->max] : []);
	}


	private function createMessage(int|string|null $min, int|string|null $max): Message
	{
		return match (true) {
			$min === $max => self::createCountMessage(self::NotExact, 'Please enter exactly %count characters.', $min),
			$max === null => self::createCountMessage(self::TooShort, 'Please enter at least %count characters.', $min),
			$min === null => self::createCountMessage(self::TooLong, 'Please enter no more than %count characters.', $max),
			default => new Message(
				self::NotInRange,
				'Please enter a value between %min and %max characters long.',
				['min' => $min, 'max' => $max],
			),
		};
	}
}
