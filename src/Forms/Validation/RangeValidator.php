<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette;
use Nette\Forms\Control;
use Nette\Utils\Validators;
use function is_int;


/**
 * Validates that the value falls within the given range (inclusive).
 * Bounds may be numbers, strings, dates or references to other form controls.
 */
final readonly class RangeValidator implements ValueValidator
{
	public const
		NotInRange = 'forms.range',
		TooSmall = 'forms.range.min',
		TooLarge = 'forms.range.max';


	public function __construct(
		public int|float|string|\DateTimeInterface|Control|null $min = null,
		public int|float|string|\DateTimeInterface|Control|null $max = null,
	) {
		if ($min === null && $max === null) {
			throw new Nette\InvalidArgumentException('At least one of the $min and $max bounds must be given.');
		}
	}


	public function validate(mixed $value): ?Message
	{
		$min = self::resolve($this->min, $value);
		$max = self::resolve($this->max, $value);
		if (Validators::isInRange($value, [$min, $max])) {
			return null;
		}

		return match (true) {
			$min !== null && $max !== null => $this->createMessage(self::NotInRange, ['min' => $min, 'max' => $max]),
			$min !== null => $this->createMessage(self::TooSmall, ['min' => $min]),
			default => $this->createMessage(self::TooLarge, ['max' => $max]),
		};
	}


	/**
	 * Resolves control references and, when a date/time value is validated,
	 * coerces string or timestamp bounds so both sides compare as date objects.
	 */
	private static function resolve(mixed $bound, mixed $value): mixed
	{
		$bound = $bound instanceof Control ? $bound->getValue() : $bound;
		if ($bound === '' || $bound === null) {
			return null;
		}

		if ($value instanceof \DateTimeInterface && !$bound instanceof \DateTimeInterface) {
			$bound = is_int($bound)
				? (new \DateTimeImmutable)->setTimestamp($bound)
				: new \DateTimeImmutable((string) $bound);
		}

		return $bound;
	}


	public function getClientRules(): array
	{
		// a control-valued bound is exported as a reference and shown as %N in the message
		$params = [
			'min' => $this->min instanceof Control ? '%0' : $this->min,
			'max' => $this->max instanceof Control ? ($this->min === null ? '%0' : '%1') : $this->max,
		];
		$attributes = array_filter(
			['min' => $this->min, 'max' => $this->max],
			fn($bound) => $bound !== null && !$bound instanceof Control,
		);

		$rule = match (true) {
			$this->min !== null && $this->max !== null => new ClientRule(
				':range',
				arg: [$this->min, $this->max],
				message: $this->createMessage(self::NotInRange, $params),
				attributes: $attributes,
			),
			$this->min !== null => new ClientRule(
				':min',
				arg: $this->min,
				message: $this->createMessage(self::TooSmall, $params),
				attributes: $attributes,
			),
			default => new ClientRule(
				':max',
				arg: $this->max,
				message: $this->createMessage(self::TooLarge, $params),
				attributes: $attributes,
			),
		};
		return [$rule];
	}


	/**
	 * @param self::NotInRange|self::TooSmall|self::TooLarge $id
	 * @param array<string, mixed> $parameters
	 */
	private function createMessage(string $id, array $parameters): Message
	{
		return match ($id) {
			self::NotInRange => new Message($id, 'Please enter a value between %min and %max.', $parameters),
			self::TooSmall => new Message($id, 'Please enter a value greater than or equal to %min.', $parameters),
			self::TooLarge => new Message($id, 'Please enter a value less than or equal to %max.', $parameters),
		};
	}
}
