<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use function count, is_array;


/**
 * Validates the number of selected items or uploaded files (for multi-value controls).
 */
final readonly class ItemCountValidator implements ValueValidator
{
	use CountBounds;

	public const
		TooFew = 'forms.count.min',
		TooMany = 'forms.count.max',
		NotExact = 'forms.count.exact',
		NotInRange = 'forms.count';


	public function validate(mixed $value): ?Message
	{
		return $this->checkCount(match (true) {
			is_array($value) => count($value),
			$value === null => 0,
			default => 1,
		});
	}


	public function getClientRule(): ClientRule
	{
		return $this->createClientRule();
	}


	private function createMessage(int|string|null $min, int|string|null $max): Message
	{
		return match (true) {
			$min === $max => self::createCountMessage(self::NotExact, 'Please select exactly %count items.', $min),
			$max === null => self::createCountMessage(self::TooFew, 'Please select at least %count items.', $min),
			$min === null => self::createCountMessage(self::TooMany, 'Please select no more than %count items.', $max),
			default => new Message(self::NotInRange, 'Please select between %min and %max items.', ['min' => $min, 'max' => $max]),
		};
	}
}
