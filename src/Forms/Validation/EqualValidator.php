<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Forms\Control;
use function is_array;


/**
 * Validates that the value equals one of the expected values (string comparison,
 * supports arrays). Expected values may reference other form controls.
 */
final readonly class EqualValidator implements ValueValidator
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
		$values = is_array($value) ? $value : [$value];
		foreach ($values as $val) {
			foreach ($this->expected as $item) {
				$item = $item instanceof Control ? $item->getValue() : $item;
				$item = $item instanceof \BackedEnum ? $item->value : $item;
				if ((string) $val === (string) $item) {
					continue 2;
				}
			}

			return $this->createMessage();
		}

		return $values === [] ? $this->createMessage() : null;
	}


	public function getClientRules(): array
	{
		// control-valued expectations are exported as references and shown as %N in the message
		$expected = implode(', ', array_map(
			fn($item, $i) => $item instanceof Control ? "%$i" : self::toText($item),
			$this->expected,
			array_keys($this->expected),
		));
		return [new ClientRule(
			':equal',
			arg: $this->expected,
			message: $this->createMessage($expected),
		)];
	}


	private function createMessage(?string $expected = null): Message
	{
		$expected ??= implode(', ', array_map(
			fn($item) => self::toText($item instanceof Control ? $item->getValue() : $item),
			$this->expected,
		));
		return new Message(self::NotEqual, 'Please enter %expected.', ['expected' => $expected]);
	}


	private static function toText(mixed $item): string
	{
		return (string) ($item instanceof \BackedEnum ? $item->value : $item);
	}
}
