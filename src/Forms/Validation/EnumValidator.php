<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette;
use function is_object, is_string;


/**
 * Validates that the value is a valid case of the given backed enum, which
 * catches invalid input before mapping form values to a typed object.
 */
final readonly class EnumValidator implements ValueValidator
{
	public const Invalid = 'forms.enum';

	/** @var class-string<\BackedEnum> */
	public string $enum;


	public function __construct(string $enum)
	{
		if (!is_a($enum, \BackedEnum::class, allow_string: true)) {
			throw new Nette\InvalidArgumentException("The Enum validator requires a backed enum class, '$enum' given.");
		}

		$this->enum = $enum;
	}


	public function validate(mixed $value): ?Message
	{
		$intBacked = (new \ReflectionEnum($this->enum))->getBackingType()?->getName() === 'int';
		foreach (is_object($value) ? [$value] : (array) $value as $item) {
			if ($intBacked) {
				$item = filter_var($item, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
				if ($item === null || $this->enum::tryFrom($item) === null) {
					return $this->createMessage();
				}
			} elseif (!is_string($item) || $this->enum::tryFrom($item) === null) {
				return $this->createMessage();
			}
		}

		return null;
	}


	private function createMessage(): Message
	{
		return new Message(self::Invalid, 'Please select a valid option.');
	}


	public function getClientRules(): array
	{
		// the enum check has no JS counterpart; export as membership check against the case values
		return [new ClientRule(
			':equal',
			arg: array_column($this->enum::cases(), 'value'),
			message: $this->createMessage(),
		)];
	}
}
