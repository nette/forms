<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette;
use function count, is_array;


/**
 * Validates the number of selected items or uploaded files (for multi-value controls).
 */
final readonly class ItemCountValidator implements ValueValidator
{
	public const
		TooFew = 'forms.count.min',
		TooMany = 'forms.count.max',
		NotExact = 'forms.count.exact';


	public function __construct(
		public ?int $min = null,
		public ?int $max = null,
	) {
		if ($min === null && $max === null) {
			throw new Nette\InvalidArgumentException('At least one of the $min and $max bounds must be given.');
		}
	}


	public function validate(mixed $value): ?Message
	{
		$count = match (true) {
			is_array($value) => count($value),
			$value === null => 0,
			default => 1,
		};
		if (($this->min === null || $count >= $this->min) && ($this->max === null || $count <= $this->max)) {
			return null;
		}

		return $this->createMessage(match (true) {
			$this->min !== null && $this->min === $this->max => self::NotExact,
			$this->min !== null && $count < $this->min => self::TooFew,
			default => self::TooMany,
		});
	}


	public function getClientRules(): array
	{
		if ($this->min !== null && $this->min === $this->max) {
			return [new ClientRule(
				':length',
				arg: [$this->min, $this->max],
				message: $this->createMessage(self::NotExact),
			)];
		}

		$rules = [];
		if ($this->min !== null) {
			$rules[] = new ClientRule(
				':minLength',
				arg: $this->min,
				message: $this->createMessage(self::TooFew),
			);
		}

		if ($this->max !== null) {
			$rules[] = new ClientRule(
				':maxLength',
				arg: $this->max,
				message: $this->createMessage(self::TooMany),
			);
		}

		return $rules;
	}


	/** @param self::NotExact|self::TooFew|self::TooMany $id */
	private function createMessage(string $id): Message
	{
		return match ($id) {
			self::NotExact => new Message($id, 'Please select exactly %count items.', count: $this->min),
			self::TooFew => new Message($id, 'Please select at least %count items.', count: $this->min),
			self::TooMany => new Message($id, 'Please select no more than %count items.', count: $this->max),
		};
	}
}
