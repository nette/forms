<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette;
use Nette\Forms\Control;
use function is_int;


/**
 * Lower and upper bound of a count (characters, items), either of them optional.
 * A bound may reference another form control.
 * @internal
 */
trait CountBounds
{
	public function __construct(
		public readonly int|Control|null $min = null,
		public readonly int|Control|null $max = null,
	) {
		if ($min === null && $max === null) {
			throw new Nette\InvalidArgumentException('At least one of the $min and $max bounds must be given.');
		}
	}


	/**
	 * Describes the failure for the given bounds: exact, at least, at most, or between.
	 */
	abstract private function createMessage(int|string|null $min, int|string|null $max): Message;


	private function checkCount(int $count): ?Message
	{
		$min = self::resolveBound($this->min);
		$max = self::resolveBound($this->max);
		return ($min === null || $count >= $min) && ($max === null || $count <= $max)
			? null
			: $this->createMessage($min, $max);
	}


	private static function resolveBound(int|Control|null $bound): ?int
	{
		$bound = $bound instanceof Control ? $bound->getValue() : $bound;
		return is_numeric($bound) ? (int) $bound : null;
	}


	/** @param  array<string, mixed>  $attributes */
	private function createClientRule(array $attributes = []): ClientRule
	{
		// a bound referencing a control is exported as a reference and shown as %N in the message
		$min = $this->min instanceof Control ? '%0' : $this->min;
		$max = $this->max instanceof Control ? ($this->min === null ? '%0' : '%1') : $this->max;
		return new ClientRule(
			match (true) {
				$this->max === null => ':minLength',
				$this->min === null => ':maxLength',
				default => ':length',
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


	/**
	 * A message about a single count, which also selects the plural form.
	 */
	private static function createCountMessage(string $id, string $template, int|string|null $count): Message
	{
		return is_int($count)
			? new Message($id, $template, count: $count)
			: new Message($id, $template, ['count' => $count]);
	}
}
