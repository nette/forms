<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette;
use Nette\Forms\Control;
use Nette\Forms\Controls\DateTimeControl;
use Nette\Utils\Validators;
use function is_int;


/**
 * Validates that the value falls within the given range (inclusive).
 * Bounds may be numbers, strings, dates or references to other form controls.
 * A date/time control compares in its own precision and a time range wraps around midnight.
 */
final readonly class RangeValidator implements ValueValidator, ControlValidator
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
		return Validators::isInRange($value, [$min, $max])
			? null
			: $this->createMessage($min, $max);
	}


	public function validateControl(Control $control): ?Message
	{
		if (!$control instanceof DateTimeControl) {
			return $this->validate($control->getValue());
		}

		$min = self::resolve($this->min, null);
		$max = self::resolve($this->max, null);
		return $control->validateMinMax($min, $max)
			? null
			: $this->createMessage($min, $max);
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


	public function getClientRule(): ClientRule
	{
		// a control-valued bound is exported as a reference and shown as %N in the message
		$min = $this->min instanceof Control ? '%0' : $this->min;
		$max = $this->max instanceof Control ? ($this->min === null ? '%0' : '%1') : $this->max;
		$attributes = array_filter(
			['min' => $this->min, 'max' => $this->max],
			fn($bound) => $bound !== null && !$bound instanceof Control,
		);

		return new ClientRule(
			match (true) {
				$this->max === null => ':min',
				$this->min === null => ':max',
				default => ':range',
			},
			arg: match (true) {
				$this->max === null => $this->min,
				$this->min === null => $this->max,
				default => [$this->min, $this->max],
			},
			message: $this->createMessage($min, $max),
			attributes: $attributes,
		);
	}


	private function createMessage(mixed $min, mixed $max): Message
	{
		return match (true) {
			$min !== null && $max !== null
				=> new Message(self::NotInRange, 'Please enter a value between %min and %max.', ['min' => $min, 'max' => $max]),
			$min !== null => new Message(self::TooSmall, 'Please enter a value greater than or equal to %min.', ['min' => $min]),
			default => new Message(self::TooLarge, 'Please enter a value less than or equal to %max.', ['max' => $max]),
		};
	}
}
