<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette;
use Nette\Utils\Strings;
use function count, is_array;


/**
 * Validates the string length or the number of items (for multi-value controls).
 */
final readonly class LengthValidator implements ValueValidator
{
	public const
		TooShort = 'forms.length.min',
		TooLong = 'forms.length.max',
		NotExact = 'forms.length.exact';


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
		$length = is_array($value) ? count($value) : Strings::length((string) $value);
		if (($this->min === null || $length >= $this->min) && ($this->max === null || $length <= $this->max)) {
			return null;
		}

		return $this->createMessage(match (true) {
			$this->min !== null && $this->min === $this->max => self::NotExact,
			$this->min !== null && $length < $this->min => self::TooShort,
			default => self::TooLong,
		});
	}


	public function getClientRules(): array
	{
		if ($this->min !== null && $this->min === $this->max) {
			return [new ClientRule(
				':length',
				arg: [$this->min, $this->max],
				message: $this->createMessage(self::NotExact),
				attributes: ['maxlength' => $this->max],
			)];
		}

		$rules = [];
		if ($this->min !== null) {
			$rules[] = new ClientRule(
				':minLength',
				arg: $this->min,
				message: $this->createMessage(self::TooShort),
			);
		}

		if ($this->max !== null) {
			$rules[] = new ClientRule(
				':maxLength',
				arg: $this->max,
				message: $this->createMessage(self::TooLong),
				attributes: ['maxlength' => $this->max],
			);
		}

		return $rules;
	}


	/** @param self::NotExact|self::TooShort|self::TooLong $id */
	private function createMessage(string $id): Message
	{
		return match ($id) {
			self::NotExact => new Message($id, 'Please enter exactly %count characters.', count: $this->min),
			self::TooShort => new Message($id, 'Please enter at least %count characters.', count: $this->min),
			self::TooLong => new Message($id, 'Please enter no more than %count characters.', count: $this->max),
		};
	}
}
