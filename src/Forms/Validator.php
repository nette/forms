<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use Nette\Utils\{Strings, Validators};
use function count, is_array, is_int, is_string;


/**
 * Default message catalog and legacy static validators.
 *
 * Message rendering lives in MessageFormatter; validator logic lives in the
 * Nette\Forms\Validation classes. The validateXxx() statics with an object
 * equivalent are deprecated thin delegates; the others remain because the
 * engine still needs them: Equal/NotEqual/Static/Valid/Submitted/Numeric
 * (operations without an object equivalent), Min/Max/Range (DateTimeControl
 * midnight wrap-around), MinLength/MaxLength/Length (item-count semantics
 * on multi-value controls) and Enum (invalid-class error path).
 */
final class Validator
{
	use Nette\StaticClass;

	/**
	 * The immutable catalog of default validation messages.
	 */
	public const Defaults = [
		Form::Equal => 'Please enter %s.',
		Form::NotEqual => 'This value should not be %s.',
		Form::Filled => 'This field is required.',
		Form::Blank => 'This field should be blank.',
		Form::MinLength => 'Please enter at least %d characters.',
		Form::MaxLength => 'Please enter no more than %d characters.',
		Form::Length => 'Please enter a value between %d and %d characters long.',
		Form::Email => 'Please enter a valid email address.',
		Form::URL => 'Please enter a valid URL.',
		Form::Enum => 'Please select a valid option.',
		Form::Integer => 'Please enter a valid integer.',
		Form::Numeric => 'Please enter a non-negative integer.',
		Form::Float => 'Please enter a valid number.',
		Form::Min => 'Please enter a value greater than or equal to %d.',
		Form::Max => 'Please enter a value less than or equal to %d.',
		Form::Range => 'Please enter a value between %d and %d.',
		Form::MaxFileSize => 'The size of the uploaded file can be up to %d bytes.',
		Form::MaxPostSize => 'The uploaded data exceeds the limit of %limit bytes.',
		Form::MimeType => 'The uploaded file is not in the expected format.',
		Form::Image => 'The uploaded file must be image in format JPEG, GIF, PNG or WebP.',
		Controls\SelectBox::Valid => 'Please select a valid option.',
		Controls\UploadControl::Valid => 'An error occurred during file upload.',
		Controls\UploadControl::MaxFiles => 'The maximum allowed number of uploaded files is %count.',
		Repeater::MinItems => 'Please add at least %count items.',
		Repeater::MaxItems => 'Please add no more than %count items.',
	];

	/**
	 * App-wide overrides of the default validation messages; a changed entry takes
	 * precedence over the default message of the mapped validator object.
	 * @var array<string, string>
	 */
	public static array $messages = self::Defaults;


	/**
	 * Renders the failure message of the given rule.
	 * @deprecated use Nette\Forms\MessageFormatter::format()
	 */
	#[\Deprecated('use Nette\Forms\MessageFormatter::format()')]
	public static function formatMessage(Rule $rule, bool $withValue = true): string|Nette\HtmlStringable
	{
		return MessageFormatter::format($rule, $withValue);
	}


	/********************* default validators ****************d*g**/


	/**
	 * Checks whether the control's value equals the argument (string comparison, supports arrays).
	 */
	public static function validateEqual(Control $control, mixed $arg): bool
	{
		return (new Validation\EqualValidator($arg))->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether the control's value does not equal the argument.
	 */
	public static function validateNotEqual(Control $control, mixed $arg): bool
	{
		return !static::validateEqual($control, $arg);
	}


	/**
	 * Always returns the argument value, used for static (constant) conditions.
	 */
	public static function validateStatic(Control $control, bool $arg): bool
	{
		return $arg;
	}


	/**
	 * Checks whether the control is filled.
	 * @deprecated use Nette\Forms\Validation\FilledValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\FilledValidator')]
	public static function validateFilled(Control $control): bool
	{
		return (new Validation\FilledValidator)->validateControl($control) === null;
	}


	/**
	 * Checks whether the control is not filled.
	 * @deprecated use Nette\Forms\Validation\BlankValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\BlankValidator')]
	public static function validateBlank(Control $control): bool
	{
		return (new Validation\BlankValidator)->validateControl($control) === null;
	}


	/**
	 * Checks whether the control passes all its validation rules (used in conditions).
	 */
	public static function validateValid(Control $control): bool
	{
		return $control->getRules()->validate();
	}


	/**
	 * Checks whether the control's value falls within the specified range (inclusive).
	 * @param  array{int|float|string|\DateTimeInterface|null, int|float|string|\DateTimeInterface|null}  $range
	 */
	public static function validateRange(Control $control, array $range): bool
	{
		if ($control instanceof Controls\DateTimeControl) {
			return $control->validateMinMax($range[0] ?? null, $range[1] ?? null);
		}
		$range = array_map(fn($v) => $v === '' ? null : $v, $range);
		return Validators::isInRange($control->getValue(), $range);
	}


	/**
	 * Checks whether the control's value is greater than or equal to the minimum.
	 */
	public static function validateMin(Control $control, int|float|string|\DateTimeInterface $minimum): bool
	{
		return Validators::isInRange($control->getValue(), [$minimum === '' ? null : $minimum, null]);
	}


	/**
	 * Checks whether the control's value is less than or equal to the maximum.
	 */
	public static function validateMax(Control $control, int|float|string|\DateTimeInterface $maximum): bool
	{
		return Validators::isInRange($control->getValue(), [null, $maximum === '' ? null : $maximum]);
	}


	/**
	 * Checks whether the string length or array count falls within the given range [min, max].
	 * @param  array{?int, ?int}|int  $range
	 */
	public static function validateLength(Control $control, array|int $range): bool
	{
		if (!is_array($range)) {
			$range = [$range, $range];
		}

		$value = $control->getValue();
		return Validators::isInRange(is_array($value) ? count($value) : Strings::length((string) $value), $range);
	}


	/**
	 * Checks whether the string length or array count is at least the specified minimum.
	 */
	public static function validateMinLength(Control $control, int $length): bool
	{
		return static::validateLength($control, [$length, null]);
	}


	/**
	 * Checks whether the string length or array count does not exceed the specified maximum.
	 */
	public static function validateMaxLength(Control $control, int $length): bool
	{
		return static::validateLength($control, [null, $length]);
	}


	/**
	 * Checks whether the submit button was used to submit the form.
	 */
	public static function validateSubmitted(Controls\SubmitButton $control): bool
	{
		return $control->isSubmittedBy();
	}


	/**
	 * Checks whether the control's value is a valid email address.
	 * @deprecated use Nette\Forms\Validation\EmailValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\EmailValidator')]
	public static function validateEmail(Control $control): bool
	{
		return (new Validation\EmailValidator)->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether the control's value is a valid URL. Auto-prepends 'https://' if the scheme is missing.
	 * @deprecated use Nette\Forms\Validation\UrlValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\UrlValidator')]
	public static function validateUrl(Control $control): bool
	{
		$validator = new Validation\UrlValidator;
		if ($validator->validate($value = $control->getValue()) !== null) {
			return false;
		}

		$control->setValue($validator->filter($value));
		return true;
	}


	/**
	 * Checks whether the control's value matches the regular expression (anchored, case-sensitive by default).
	 * @deprecated use Nette\Forms\Validation\PatternValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\PatternValidator')]
	public static function validatePattern(Control $control, string $pattern, bool $caseInsensitive = false): bool
	{
		return (new Validation\PatternValidator($pattern, $caseInsensitive))->validate($control->getValue()) === null;
	}


	/** @deprecated use Nette\Forms\Validation\PatternValidator */
	#[\Deprecated('use Nette\Forms\Validation\PatternValidator')]
	public static function validatePatternCaseInsensitive(Control $control, string $pattern): bool
	{
		return (new Validation\PatternValidator($pattern, caseInsensitive: true))->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether the control's value is a valid case of the given backed enum.
	 * @param  class-string  $enum  backed enum class
	 */
	public static function validateEnum(Control $control, string $enum): bool
	{
		return (new Validation\EnumValidator($enum))->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether the control's value is a non-negative integer string or int.
	 */
	public static function validateNumeric(Control $control): bool
	{
		$value = $control->getValue();
		return (is_int($value) && $value >= 0)
			|| (is_string($value) && Strings::match($value, '#^\d+$#D'));
	}


	/**
	 * Checks whether the control's value is an integer. Normalizes the value by casting it to int.
	 * @deprecated use Nette\Forms\Validation\IntegerValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\IntegerValidator')]
	public static function validateInteger(Control $control): bool
	{
		$validator = new Validation\IntegerValidator;
		if ($validator->validate($value = $control->getValue()) !== null) {
			return false;
		}

		$control->setValue($validator->filter($value));
		return true;
	}


	/**
	 * Checks whether the control's value is a number. Normalizes spaces and commas and casts it to float.
	 * @deprecated use Nette\Forms\Validation\FloatValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\FloatValidator')]
	public static function validateFloat(Control $control): bool
	{
		$validator = new Validation\FloatValidator;
		if ($validator->validate($value = $control->getValue()) !== null) {
			return false;
		}

		$control->setValue($validator->filter($value));
		return true;
	}


	/**
	 * Checks whether all uploaded files are within the size limit (in bytes).
	 * @deprecated use Nette\Forms\Validation\FileSizeValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\FileSizeValidator')]
	public static function validateFileSize(Controls\UploadControl $control, int $limit): bool
	{
		return (new Validation\FileSizeValidator($limit))->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether all uploaded files match one of the allowed MIME types (wildcards like 'image/*' are supported).
	 * @param  string|string[]  $mimeType
	 * @deprecated use Nette\Forms\Validation\MimeTypeValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\MimeTypeValidator')]
	public static function validateMimeType(Controls\UploadControl $control, string|array $mimeType): bool
	{
		return (new Validation\MimeTypeValidator($mimeType))->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether all uploaded files are images (JPEG, PNG, GIF, WebP, AVIF).
	 * @deprecated use Nette\Forms\Validation\ImageValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\ImageValidator')]
	public static function validateImage(Controls\UploadControl $control): bool
	{
		return (new Validation\ImageValidator)->validate($control->getValue()) === null;
	}
}
