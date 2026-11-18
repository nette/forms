<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use function is_array;


/**
 * App-wide overrides of validation messages and legacy static validators.
 *
 * The default messages live with the validator objects in Nette\Forms\Validation,
 * message rendering in MessageFormatter. The validateXxx() statics with an object
 * equivalent are deprecated thin delegates.
 */
final class Validator
{
	use Nette\StaticClass;

	/**
	 * App-wide overrides of the default validation messages, keyed by the message
	 * identifier (e.g. 'forms.length.min') or by the operation it belongs to (Form::MinLength).
	 * @var array<string, string>
	 */
	public static array $messages = [];


	/**
	 * Renders the failure message of the given rule.
	 * @deprecated use Nette\Forms\MessageFormatter::format()
	 */
	#[\Deprecated('use Nette\Forms\MessageFormatter::format()', since: '4.0')]
	public static function formatMessage(Rule $rule, bool $withValue = true): string|Nette\HtmlStringable
	{
		return MessageFormatter::format($rule, $withValue, $rule->getClientRule()?->message);
	}


	/********************* default validators ****************d*g**/


	/**
	 * Checks whether the control's value equals the argument (string comparison, supports arrays).
	 * @deprecated use Nette\Forms\Validation\EqualValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\EqualValidator', since: '4.0')]
	public static function validateEqual(Control $control, mixed $arg): bool
	{
		return new Validation\EqualValidator($arg)->validateControl($control) === null;
	}


	/**
	 * Checks whether the control's value does not equal the argument.
	 * @deprecated use Nette\Forms\Validation\NotEqualValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\NotEqualValidator', since: '4.0')]
	public static function validateNotEqual(Control $control, mixed $arg): bool
	{
		return new Validation\NotEqualValidator($arg)->validateControl($control) === null;
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
	#[\Deprecated('use Nette\Forms\Validation\FilledValidator', since: '4.0')]
	public static function validateFilled(Control $control): bool
	{
		return (new Validation\FilledValidator)->validateControl($control) === null;
	}


	/**
	 * Checks whether the control is not filled.
	 * @deprecated use Nette\Forms\Validation\BlankValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\BlankValidator', since: '4.0')]
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
	 * @deprecated use Nette\Forms\Validation\RangeValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\RangeValidator', since: '4.0')]
	public static function validateRange(Control $control, array $range): bool
	{
		[$min, $max] = [$range[0] ?? null, $range[1] ?? null];
		return ($min !== null || $max !== null)
			&& new Validation\RangeValidator($min, $max)->validateControl($control) === null;
	}


	/**
	 * Checks whether the control's value is greater than or equal to the minimum.
	 * @deprecated use Nette\Forms\Validation\RangeValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\RangeValidator', since: '4.0')]
	public static function validateMin(Control $control, int|float|string|\DateTimeInterface $minimum): bool
	{
		return new Validation\RangeValidator(min: $minimum)->validateControl($control) === null;
	}


	/**
	 * Checks whether the control's value is less than or equal to the maximum.
	 * @deprecated use Nette\Forms\Validation\RangeValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\RangeValidator', since: '4.0')]
	public static function validateMax(Control $control, int|float|string|\DateTimeInterface $maximum): bool
	{
		return new Validation\RangeValidator(max: $maximum)->validateControl($control) === null;
	}


	/**
	 * Checks whether the string length or array count falls within the given range [min, max].
	 * @param  array{?int, ?int}|int  $range
	 * @deprecated use Nette\Forms\Validation\LengthValidator or ItemCountValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\LengthValidator or ItemCountValidator', since: '4.0')]
	public static function validateLength(Control $control, array|int $range): bool
	{
		return is_array($range)
			? self::checkLength($control, $range[0] ?? null, $range[1] ?? null)
			: self::checkLength($control, $range, $range);
	}


	/**
	 * Checks whether the string length or array count is at least the specified minimum.
	 * @deprecated use Nette\Forms\Validation\LengthValidator or ItemCountValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\LengthValidator or ItemCountValidator', since: '4.0')]
	public static function validateMinLength(Control $control, int $length): bool
	{
		return self::checkLength($control, $length, null);
	}


	/**
	 * Checks whether the string length or array count does not exceed the specified maximum.
	 * @deprecated use Nette\Forms\Validation\LengthValidator or ItemCountValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\LengthValidator or ItemCountValidator', since: '4.0')]
	public static function validateMaxLength(Control $control, int $length): bool
	{
		return self::checkLength($control, null, $length);
	}


	private static function checkLength(Control $control, ?int $min, ?int $max): bool
	{
		$value = $control->getValue();
		$validator = is_array($value)
			? new Validation\ItemCountValidator($min, $max)
			: new Validation\LengthValidator($min, $max);
		return $validator->validate($value) === null;
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
	#[\Deprecated('use Nette\Forms\Validation\EmailValidator', since: '4.0')]
	public static function validateEmail(Control $control): bool
	{
		return (new Validation\EmailValidator)->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether the control's value is a valid URL. Auto-prepends 'https://' if the scheme is missing.
	 * @deprecated use Nette\Forms\Validation\UrlValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\UrlValidator', since: '4.0')]
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
	#[\Deprecated('use Nette\Forms\Validation\PatternValidator', since: '4.0')]
	public static function validatePattern(Control $control, string $pattern, bool $caseInsensitive = false): bool
	{
		return new Validation\PatternValidator($pattern, $caseInsensitive)->validate($control->getValue()) === null;
	}


	/** @deprecated use Nette\Forms\Validation\PatternValidator */
	#[\Deprecated('use Nette\Forms\Validation\PatternValidator', since: '4.0')]
	public static function validatePatternCaseInsensitive(Control $control, string $pattern): bool
	{
		return new Validation\PatternValidator($pattern, caseInsensitive: true)->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether the control's value is a valid case of the given backed enum.
	 * @param  class-string  $enum  backed enum class
	 * @deprecated use Nette\Forms\Validation\EnumValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\EnumValidator', since: '4.0')]
	public static function validateEnum(Control $control, string $enum): bool
	{
		return new Validation\EnumValidator($enum)->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether the control's value is a non-negative integer string or int.
	 * @deprecated use Nette\Forms\Validation\NumericValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\NumericValidator', since: '4.0')]
	public static function validateNumeric(Control $control): bool
	{
		return (new Validation\NumericValidator)->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether the control's value is an integer. Normalizes the value by casting it to int.
	 * @deprecated use Nette\Forms\Validation\IntegerValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\IntegerValidator', since: '4.0')]
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
	#[\Deprecated('use Nette\Forms\Validation\FloatValidator', since: '4.0')]
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
	#[\Deprecated('use Nette\Forms\Validation\FileSizeValidator', since: '4.0')]
	public static function validateFileSize(Controls\UploadControl $control, int $limit): bool
	{
		return new Validation\FileSizeValidator($limit)->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether all uploaded files match one of the allowed MIME types (wildcards like 'image/*' are supported).
	 * @param  string|string[]  $mimeType
	 * @deprecated use Nette\Forms\Validation\MimeTypeValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\MimeTypeValidator', since: '4.0')]
	public static function validateMimeType(Controls\UploadControl $control, string|array $mimeType): bool
	{
		return new Validation\MimeTypeValidator($mimeType)->validate($control->getValue()) === null;
	}


	/**
	 * Checks whether all uploaded files are images (JPEG, PNG, GIF, WebP, AVIF).
	 * @deprecated use Nette\Forms\Validation\ImageValidator
	 */
	#[\Deprecated('use Nette\Forms\Validation\ImageValidator', since: '4.0')]
	public static function validateImage(Controls\UploadControl $control): bool
	{
		return (new Validation\ImageValidator)->validate($control->getValue()) === null;
	}
}
