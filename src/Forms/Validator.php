<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use function array_map, count, explode, in_array, is_array, is_float, is_int, is_object, is_string, preg_replace, preg_replace_callback, rtrim, str_replace, strtolower;


/**
 * Common validators.
 */
final class Validator
{
	use Nette\StaticClass;

	/** @var array<string, string> */
	public static array $messages = [
		Controls\CsrfProtection::Protection => 'Your session has expired. Please return to the home page and try again.',
		Form::Equal => 'Please enter %s.',
		Form::NotEqual => 'This value should not be %s.',
		Form::Filled => 'This field is required.',
		Form::Blank => 'This field should be blank.',
		Form::MinLength => 'Please enter at least %d characters.',
		Form::MaxLength => 'Please enter no more than %d characters.',
		Form::Length => 'Please enter a value between %d and %d characters long.',
		Form::Email => 'Please enter a valid email address.',
		Form::URL => 'Please enter a valid URL.',
		Form::Integer => 'Please enter a valid integer.',
		Form::Float => 'Please enter a valid number.',
		Form::Min => 'Please enter a value greater than or equal to %d.',
		Form::Max => 'Please enter a value less than or equal to %d.',
		Form::Range => 'Please enter a value between %d and %d.',
		Form::MaxFileSize => 'The size of the uploaded file can be up to %d bytes.',
		Form::MaxPostSize => 'The uploaded data exceeds the limit of %d bytes.',
		Form::MimeType => 'The uploaded file is not in the expected format.',
		Form::Image => 'The uploaded file must be image in format JPEG, GIF, PNG or WebP.',
		Controls\SelectBox::Valid => 'Please select a valid option.',
		Controls\UploadControl::Valid => 'An error occurred during file upload.',
	];


	/**
	 * @internal
	 */
	public static function formatMessage(Rule $rule, bool $withValue = true): string|Nette\HtmlStringable
	{
		$message = $rule->message;
		if ($message instanceof Nette\HtmlStringable) {
			return $message;

		} elseif ($message === null && is_string($rule->validator) && isset(static::$messages[$rule->validator])) {
			$message = static::$messages[$rule->validator];

		} elseif ($message == null) { // intentionally ==
			trigger_error(
				"Missing validation message for control '{$rule->control->getName()}'"
				. (is_string($rule->validator) ? " (validator '{$rule->validator}')." : '.'),
				E_USER_WARNING,
			);
		}

		if ($translator = $rule->control->getForm()->getTranslator()) {
			$message = $translator->translate($message, is_int($rule->arg) ? $rule->arg : null);
		}

		$message = preg_replace_callback('#%(name|label|value|\d+\$[ds]|[ds])#', function (array $m) use ($rule, $withValue, $translator) {
			static $i = -1;
			switch ($m[1]) {
				case 'name': return $rule->control->getName();
				case 'label':
					if ($rule->control instanceof Controls\BaseControl) {
						$caption = $rule->control->getCaption();
						$caption = match (true) {
							$caption instanceof Nette\Utils\Html => $caption->getText(),
							$caption instanceof Nette\HtmlStringable => (string) $caption,
							$translator !== null => $translator->translate($caption),
							default => $caption,
						};
						return rtrim((string) $caption, ':');
					}

					return '';
				case 'value': return $withValue
						? $rule->control->getValue()
						: $m[0];
				default:
					$args = is_array($rule->arg) ? $rule->arg : [$rule->arg];
					$i = (int) $m[1] ? (int) $m[1] - 1 : $i + 1;
					$arg = $args[$i] ?? null;
					if ($arg === null) {
						return '';
					} elseif ($arg instanceof Control) {
						return $withValue ? $args[$i]->getValue() : "%$i";
					} elseif ($rule->control instanceof Controls\DateTimeControl) {
						return $rule->control->formatLocaleText($arg);
					} else {
						return $arg;
					}
			}
		}, $message);
		return $message;
	}


	/********************* default validators ****************d*g**/


	/**
	 * Checks whether the control's value equals the argument (string comparison, supports arrays).
	 */
	public static function validateEqual(Control $control, mixed $arg): bool
	{
		$value = $control->getValue();
		$values = is_array($value) ? $value : [$value];
		$args = is_array($arg) ? $arg : [$arg];

		foreach ($values as $val) {
			foreach ($args as $item) {
				if ($item instanceof \BackedEnum) {
					$item = $item->value;
				}

				if ((string) $val === (string) $item) {
					continue 2;
				}
			}

			return false;
		}

		return (bool) $values;
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
	 */
	public static function validateFilled(Controls\BaseControl $control): bool
	{
		return $control->isFilled();
	}


	/**
	 * Checks whether the control is not filled.
	 */
	public static function validateBlank(Controls\BaseControl $control): bool
	{
		return !$control->isFilled();
	}


	/**
	 * Checks whether the control passes all its validation rules (used in conditions).
	 */
	public static function validateValid(Controls\BaseControl $control): bool
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
	 */
	public static function validateEmail(Control $control): bool
	{
		return Validators::isEmail((string) $control->getValue());
	}


	/**
	 * Checks whether the control's value is a valid URL. Auto-prepends 'https://' if the scheme is missing.
	 */
	public static function validateUrl(Control $control): bool
	{
		$value = (string) $control->getValue();
		if (Validators::isUrl($value)) {
			return true;
		}

		$value = "https://$value";
		if (Validators::isUrl($value)) {
			$control->setValue($value);
			return true;
		}

		return false;
	}


	/**
	 * Checks whether the control's value matches the regular expression (anchored, case-sensitive by default).
	 */
	public static function validatePattern(Control $control, string $pattern, bool $caseInsensitive = false): bool
	{
		$regexp = "\x01^(?:$pattern)$\x01Du" . ($caseInsensitive ? 'i' : '');
		foreach (static::toArray($control->getValue()) as $item) {
			$value = $item instanceof Nette\Http\FileUpload ? $item->getUntrustedName() : $item;
			if (!Strings::match((string) $value, $regexp)) {
				return false;
			}
		}

		return true;
	}


	public static function validatePatternCaseInsensitive(Control $control, string $pattern): bool
	{
		return self::validatePattern($control, $pattern, caseInsensitive: true);
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
	 */
	public static function validateInteger(Control $control): bool
	{
		if (
			Validators::isNumericInt($value = $control->getValue())
			&& !is_float($tmp = $value * 1) // too big for int?
		) {
			$control->setValue($tmp);
			return true;
		}

		return false;
	}


	/**
	 * Checks whether the control's value is a number. Normalizes spaces and commas and casts it to float.
	 */
	public static function validateFloat(Control $control): bool
	{
		$value = $control->getValue();
		if (is_string($value)) {
			$value = str_replace([' ', ','], ['', '.'], $value);
		}

		if (Validators::isNumeric($value)) {
			$control->setValue((float) $value);
			return true;
		}

		return false;
	}


	/**
	 * Checks whether all uploaded files are within the size limit (in bytes).
	 */
	public static function validateFileSize(Controls\UploadControl $control, int $limit): bool
	{
		foreach (static::toArray($control->getValue()) as $file) {
			if ($file->getSize() > $limit || $file->getError() === UPLOAD_ERR_INI_SIZE) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Checks whether all uploaded files match one of the allowed MIME types (wildcards like 'image/*' are supported).
	 * @param  string|string[]  $mimeType
	 */
	public static function validateMimeType(Controls\UploadControl $control, string|array $mimeType): bool
	{
		$mimeTypes = is_array($mimeType) ? $mimeType : explode(',', $mimeType);
		foreach (static::toArray($control->getValue()) as $file) {
			$type = strtolower($file->getContentType() ?? '');
			if (!in_array($type, $mimeTypes, true) && !in_array(preg_replace('#/.*#', '/*', $type), $mimeTypes, true)) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Checks whether all uploaded files are images (JPEG, PNG, GIF, WebP, AVIF).
	 */
	public static function validateImage(Controls\UploadControl $control): bool
	{
		foreach (static::toArray($control->getValue()) as $file) {
			if (!$file->isImage()) {
				return false;
			}
		}

		return true;
	}


	/** @return mixed[] */
	private static function toArray(mixed $value): array
	{
		return is_object($value) ? [$value] : (array) $value;
	}
}
