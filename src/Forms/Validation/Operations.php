<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette;
use Nette\Forms\{Control, Form};
use Nette\Forms\Controls\DateTimeControl;
use function is_array, is_bool, is_float, is_int, is_string, sprintf;


/**
 * Maps string operations (Form::Email, ':minLength'...) to the shared validator
 * objects implementing them, so that all rules get stable message identifiers
 * and a single evaluation path. Returns null when the operation or its argument
 * has no object counterpart; such rules run through the classic static validators.
 * @internal
 */
final class Operations
{
	use Nette\StaticClass;

	public static function toValidator(string $op, mixed $arg, Control $control): ?Validator
	{
		if ($control instanceof DateTimeControl && in_array($op, [Form::Min, Form::Max, Form::Range], true)) {
			return null; // DateTimeControl has its own range semantics (midnight wrap-around)
		}

		$isBound = fn(mixed $value): bool => $value === null
			|| is_int($value) || is_float($value) || is_string($value)
			|| $value instanceof \DateTimeInterface || $value instanceof Control;
		$isText = $control instanceof Nette\Forms\Controls\TextBase;

		return match (true) {
			$op === Form::Filled => new FilledValidator,
			$op === Form::Blank => new BlankValidator,
			$op === Form::Email => new EmailValidator,
			$op === Form::URL => new UrlValidator,
			$op === Form::Integer => new IntegerValidator,
			$op === Form::Float => new FloatValidator,
			$op === Form::Equal => new EqualValidator($arg),
			$op === Form::Min && $isBound($arg) && $arg !== null => new RangeValidator(min: $arg),
			$op === Form::Max && $isBound($arg) && $arg !== null => new RangeValidator(max: $arg),
			$op === Form::Range && is_array($arg) && $isBound($arg[0] ?? null) && $isBound($arg[1] ?? null)
				=> new RangeValidator($arg[0] ?? null, $arg[1] ?? null),
			// for multi-value controls the length means the number of items, which the classic validators handle
			$op === Form::MinLength && $isText && is_int($arg) => new LengthValidator(min: $arg),
			$op === Form::MaxLength && $isText && is_int($arg) => new LengthValidator(max: $arg),
			$op === Form::Length && $isText && is_int($arg) => new LengthValidator($arg, $arg),
			$op === Form::Length && $isText && self::isIntBounds($arg) => new LengthValidator($arg[0] ?? null, $arg[1] ?? null),
			$op === Form::Pattern && is_string($arg) => new PatternValidator($arg),
			$op === Form::PatternInsensitive && is_string($arg) => new PatternValidator($arg, caseInsensitive: true),
			$op === Form::Enum && is_string($arg) && is_a($arg, \BackedEnum::class, allow_string: true) => new EnumValidator($arg),
			$op === Form::MimeType && (is_string($arg) || is_array($arg)) => new MimeTypeValidator($arg),
			$op === Form::Image => new ImageValidator,
			$op === Form::MaxFileSize && is_int($arg) => new FileSizeValidator($arg),
			default => null,
		};
	}


	/**
	 * Two-element array of int bounds where at least one is given, optionally
	 * also bounds referencing controls.
	 */
	private static function isIntBounds(mixed $value, bool $orControl = false): bool
	{
		if (!is_array($value)) {
			return false;
		}

		$isBound = fn($bound) => $bound === null || is_int($bound) || ($orControl && $bound instanceof Control);
		$min = $value[0] ?? null;
		$max = $value[1] ?? null;
		return $isBound($min) && $isBound($max) && ($min !== null || $max !== null);
	}


	/**
	 * Verifies the argument type of a string operation at declaration time, so that a mistyped
	 * argument fails in addRule() instead of a TypeError at submit time. A Control stands for its value.
	 */
	public static function assertArgument(string $op, mixed $arg, Control $control): void
	{
		$expected = match ($op) {
			':static' => is_bool($arg) ? null : 'bool',
			Form::Min, Form::Max => is_int($arg)
				|| is_float($arg)
				|| is_string($arg)
				|| $arg instanceof \DateTimeInterface
				|| $arg instanceof Control
				? null
				: 'int, float, string, date/time or Control',
			Form::Range => is_array($arg) ? null : 'array of two bounds',
			Form::MinLength, Form::MaxLength => is_int($arg) || $arg instanceof Control ? null : 'int',
			Form::Length => is_int($arg) || self::isIntBounds($arg, orControl: true) || $arg instanceof Control
				? null
				: 'int or array of two int bounds',
			Form::Pattern, Form::PatternInsensitive => is_string($arg) || $arg instanceof Control ? null : 'string pattern',
			Form::Enum => is_string($arg) || $arg instanceof Control ? null : 'class name of a backed enum',
			Form::MimeType => is_string($arg) || is_array($arg) || $arg instanceof Control ? null : 'string or array of strings',
			Form::MaxFileSize => is_int($arg) || $arg instanceof Control ? null : 'int (bytes)',
			default => null,
		};

		if ($expected !== null) {
			throw new Nette\InvalidArgumentException(sprintf(
				"Validation rule %s for control '%s' expects %s as the argument, %s given.",
				$op,
				(string) $control->getName(),
				$expected,
				get_debug_type($arg),
			));
		}
	}
}
