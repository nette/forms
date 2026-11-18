<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette;
use Nette\Forms\{Control, Form};
use Nette\Forms\Controls\DateTimeControl;
use function count, is_array, is_bool, is_float, is_int, is_string, sprintf;


/**
 * Maps string operations (Form::Email, ':minLength'...) to the validator objects
 * implementing them, so that all rules get stable message identifiers and a single
 * evaluation path. Only the operations :static, :submitted and :valid have no object.
 * @internal
 */
final class Operations
{
	use Nette\StaticClass;

	/** operations whose validator class is used by no other operation */
	private const Classes = [
		Form::Filled => FilledValidator::class,
		Form::Blank => BlankValidator::class,
		Form::Email => EmailValidator::class,
		Form::URL => UrlValidator::class,
		Form::Integer => IntegerValidator::class,
		Form::Float => FloatValidator::class,
		Form::Numeric => NumericValidator::class,
		Form::Equal => EqualValidator::class,
		Form::NotEqual => NotEqualValidator::class,
		Form::Enum => EnumValidator::class,
		Form::MimeType => MimeTypeValidator::class,
		Form::Image => ImageValidator::class,
		Form::MaxFileSize => FileSizeValidator::class,
	];

	/** message identifiers of the validators and the operations they belong to */
	private const MessageOperations = [
		FilledValidator::Required => Form::Filled,
		BlankValidator::NotBlank => Form::Blank,
		EmailValidator::InvalidSyntax => Form::Email,
		UrlValidator::NotUrl => Form::URL,
		IntegerValidator::NotInteger => Form::Integer,
		FloatValidator::NotNumber => Form::Float,
		NumericValidator::NotNumeric => Form::Numeric,
		EqualValidator::NotEqual => Form::Equal,
		NotEqualValidator::Equal => Form::NotEqual,
		EnumValidator::Invalid => Form::Enum,
		PatternValidator::Mismatch => Form::Pattern,
		RangeValidator::NotInRange => Form::Range,
		RangeValidator::TooSmall => Form::Min,
		RangeValidator::TooLarge => Form::Max,
		LengthValidator::NotInRange => Form::Length,
		LengthValidator::NotExact => Form::Length,
		LengthValidator::TooShort => Form::MinLength,
		LengthValidator::TooLong => Form::MaxLength,
		ItemCountValidator::NotInRange => Form::Length,
		ItemCountValidator::NotExact => Form::Length,
		ItemCountValidator::TooFew => Form::MinLength,
		ItemCountValidator::TooMany => Form::MaxLength,
		FileSizeValidator::TooBig => Form::MaxFileSize,
		MimeTypeValidator::UnexpectedType => Form::MimeType,
		ImageValidator::NotImage => Form::Image,
	];


	/**
	 * Creates the validator performing the operation. The argument must have passed assertArgument().
	 */
	public static function toValidator(string $op, mixed $arg, Control $control): ?Validator
	{
		if ($control instanceof DateTimeControl && in_array($op, [Form::Equal, Form::NotEqual, Form::Min, Form::Max, Form::Range], true)) {
			$arg = is_array($arg) ? array_map(self::toDateTime(...), $arg) : self::toDateTime($arg);
		}

		// for multi-value controls the length rules mean the number of items
		$isMulti = $control instanceof Nette\Forms\Controls\MultiChoiceControl
			|| ($control instanceof Nette\Forms\Controls\UploadControl && $control->getControlPrototype()->multiple);
		$bounds = is_array($arg) ? [$arg[0] ?? null, $arg[1] ?? null] : [$arg, $arg];

		return match ($op) {
			Form::Filled => new FilledValidator,
			Form::Blank => new BlankValidator,
			Form::Email => new EmailValidator,
			Form::URL => new UrlValidator,
			Form::Integer => new IntegerValidator,
			Form::Float => new FloatValidator,
			Form::Numeric => new NumericValidator,
			Form::Equal => new EqualValidator($arg),
			Form::NotEqual => new NotEqualValidator($arg),
			Form::Min => new RangeValidator(min: $arg),
			Form::Max => new RangeValidator(max: $arg),
			Form::Range => new RangeValidator(...$bounds),
			Form::MinLength => $isMulti ? new ItemCountValidator(min: $arg) : new LengthValidator(min: $arg),
			Form::MaxLength => $isMulti ? new ItemCountValidator(max: $arg) : new LengthValidator(max: $arg),
			Form::Length => $isMulti ? new ItemCountValidator(...$bounds) : new LengthValidator(...$bounds),
			Form::Pattern => new PatternValidator($arg),
			Form::PatternInsensitive => new PatternValidator($arg, caseInsensitive: true),
			Form::Enum => new EnumValidator($arg),
			Form::MimeType => new MimeTypeValidator($arg),
			Form::Image => new ImageValidator,
			Form::MaxFileSize => new FileSizeValidator($arg),
			default => null,
		};
	}


	/**
	 * Date/time bounds of a date/time control are objects, so that messages show them in its locale format.
	 */
	private static function toDateTime(mixed $value): mixed
	{
		return match (true) {
			is_int($value) => (new \DateTimeImmutable)->setTimestamp($value),
			is_string($value) && $value !== '' => new \DateTimeImmutable($value),
			default => $value,
		};
	}


	/**
	 * Returns the validator class of an operation, unless the class is shared by several operations.
	 * @return ?class-string<Validator>
	 */
	public static function getValidatorClass(string $op): ?string
	{
		return self::Classes[$op] ?? null;
	}


	/**
	 * Returns the operation a message identifier belongs to.
	 */
	public static function getMessageOperation(string $id): ?string
	{
		return self::MessageOperations[$id] ?? null;
	}


	/**
	 * Verifies the argument of a string operation at declaration time, so that a mistyped
	 * argument fails in addRule() instead of at submit time. A reference to another control
	 * is checked when the form is validated.
	 */
	public static function assertArgument(string $op, mixed $arg, Control $control): void
	{
		$isBound = fn(mixed $value): bool => is_int($value)
			|| is_float($value)
			|| is_string($value)
			|| $value instanceof \DateTimeInterface
			|| $value instanceof Control;

		$expected = match ($op) {
			':static' => is_bool($arg) ? null : 'bool',
			Form::Min, Form::Max => $isBound($arg) ? null : 'int, float, string, date/time or Control',
			Form::Range => is_array($arg)
				&& count($arg) === 2
				&& array_all($arg, fn($bound) => $bound === null || $isBound($bound))
				&& ($arg[0] ?? $arg[1] ?? null) !== null
				? null
				: 'array of two bounds',
			Form::MinLength, Form::MaxLength => is_int($arg) || $arg instanceof Control ? null : 'int',
			Form::Length => is_int($arg) || self::isIntBounds($arg) || $arg instanceof Control
				? null
				: 'int or array of two int bounds',
			Form::Pattern, Form::PatternInsensitive => is_string($arg) || $arg instanceof Control ? null : 'string pattern',
			Form::Enum => is_string($arg) && is_a($arg, \BackedEnum::class, allow_string: true) ? null : 'class name of a backed enum',
			Form::MimeType => is_string($arg) || (is_array($arg) && $arg !== [] && array_all($arg, fn($type) => is_string($type)))
				? null
				: 'string or array of strings',
			Form::MaxFileSize => is_int($arg) ? null : 'int (bytes)',
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


	/**
	 * Two-element array of int bounds or control references, at least one of them given.
	 */
	private static function isIntBounds(mixed $value): bool
	{
		if (!is_array($value)) {
			return false;
		}

		$isBound = fn($bound) => $bound === null || is_int($bound) || $bound instanceof Control;
		$min = $value[0] ?? null;
		$max = $value[1] ?? null;
		return $isBound($min) && $isBound($max) && ($min !== null || $max !== null);
	}
}
