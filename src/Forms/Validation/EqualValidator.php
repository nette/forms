<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Forms\Control;
use Nette\Forms\Controls\DateTimeControl;
use function is_array;


/**
 * Validates that the value equals one of the expected values (string comparison,
 * supports arrays). Expected values may reference other form controls. A date/time
 * control compares in its own precision.
 */
final readonly class EqualValidator implements ValueValidator, ControlValidator
{
	public const NotEqual = 'forms.equal';

	/** @var list<mixed> */
	public array $expected;


	public function __construct(mixed $expected)
	{
		$this->expected = is_array($expected) ? array_values($expected) : [$expected];
	}


	public function validate(mixed $value): ?Message
	{
		return $this->matches($value) ? null : $this->createMessage();
	}


	public function validateControl(Control $control): ?Message
	{
		if ($control instanceof DateTimeControl) {
			return $control->validateEqual($this->resolveExpected()) ? null : $this->createMessage();
		}

		return $this->validate($control->getValue());
	}


	/**
	 * Does every item of the value equal one of the expected values?
	 */
	private function matches(mixed $value): bool
	{
		$values = is_array($value) ? $value : [$value];
		foreach ($values as $val) {
			foreach ($this->resolveExpected() as $item) {
				$item = $item instanceof \BackedEnum ? $item->value : $item;
				if ((string) $val === (string) $item) {
					continue 2;
				}
			}

			return false;
		}

		return $values !== [];
	}


	/**
	 * Expected values with control references replaced by the values of the controls.
	 * @internal
	 * @return list<mixed>
	 */
	public function resolveExpected(): array
	{
		return array_map(fn($item) => $item instanceof Control ? $item->getValue() : $item, $this->expected);
	}


	/**
	 * Expected values for the client, control references shown as %N placeholders.
	 * @internal
	 * @return list<mixed>
	 */
	public function describeExpected(): array
	{
		return array_map(fn($item, $i) => $item instanceof Control ? "%$i" : $item, $this->expected, array_keys($this->expected));
	}


	public function getClientRule(): ClientRule
	{
		return new ClientRule(
			':equal',
			arg: $this->expected,
			message: $this->createMessage($this->describeExpected()),
		);
	}


	/** @param  ?list<mixed>  $expected */
	private function createMessage(?array $expected = null): Message
	{
		return new Message(self::NotEqual, 'Please enter %expected.', ['expected' => $expected ?? $this->resolveExpected()]);
	}
}
