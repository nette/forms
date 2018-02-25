<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use Nette\Utils\Strings;
use Nette\Utils\Validators;


/**
 * Common validators.
 */
class Validator
{
	use Nette\StaticClass;

	/** @var array */
	public static $messages = [
		Form::PROTECTION => 'Your session has expired. Please return to the home page and try again.',
		Form::EQUAL => 'Please enter %s.',
		Form::NOT_EQUAL => 'This value should not be %s.',
		Form::FILLED => 'This field is required.',
		Form::BLANK => 'This field should be blank.',
		Form::MIN_LENGTH => 'Please enter at least %d characters.',
		Form::MAX_LENGTH => 'Please enter no more than %d characters.',
		Form::LENGTH => 'Please enter a value between %d and %d characters long.',
		Form::EMAIL => 'Please enter a valid email address.',
		Form::URL => 'Please enter a valid URL.',
		Form::INTEGER => 'Please enter a valid integer.',
		Form::FLOAT => 'Please enter a valid number.',
		Form::MIN => 'Please enter a value greater than or equal to %d.',
		Form::MAX => 'Please enter a value less than or equal to %d.',
		Form::RANGE => 'Please enter a value between %d and %d.',
		Form::MAX_FILE_SIZE => 'The size of the uploaded file can be up to %d bytes.',
		Form::MAX_POST_SIZE => 'The uploaded data exceeds the limit of %d bytes.',
		Form::MIME_TYPE => 'The uploaded file is not in the expected format.',
		Form::IMAGE => 'The uploaded file must be image in format JPEG, GIF or PNG.',
		Controls\SelectBox::VALID => 'Please select a valid option.',
		Controls\UploadControl::VALID => 'An error occurred during file upload.',
	];


	/** @internal */
	public static function formatMessage(Rule $rule, $withValue = true)
	{
		$message = $rule->message;
		if ($message instanceof Nette\Utils\IHtmlString) {
			return $message;

		} elseif ($message === null && is_string($rule->validator) && isset(static::$messages[$rule->validator])) {
			$message = static::$messages[$rule->validator];

		} elseif ($message == null) { // intentionally ==
			trigger_error("Missing validation message for control '{$rule->control->getName()}'.", E_USER_WARNING);
		}

		if ($translator = $rule->control->getForm()->getTranslator()) {
			$message = $translator->translate($message, is_int($rule->arg) ? $rule->arg : null);
		}

		$message = preg_replace_callback('#%(name|label|value|\d+\$[ds]|[ds])#', function ($m) use ($rule, $withValue) {
			static $i = -1;
			switch ($m[1]) {
				case 'name': return $rule->control->getName();
				case 'label': return $rule->control instanceof Controls\BaseControl ? $rule->control->translate($rule->control->caption) : null;
				case 'value': return $withValue ? $rule->control->getValue() : $m[0];
				default:
					$args = is_array($rule->arg) ? $rule->arg : [$rule->arg];
					$i = (int) $m[1] ? (int) $m[1] - 1 : $i + 1;
					return isset($args[$i]) ? ($args[$i] instanceof IControl ? ($withValue ? $args[$i]->getValue() : "%$i") : $args[$i]) : '';
			}
		}, $message);
		return $message;
	}


	/********************* default validators ****************d*g**/


	/**
	 * Is control's value equal with second parameter?
	 * @return bool
	 */
	public static function validateEqual(IControl $control, $arg)
	{
		$value = $control->getValue();
		foreach ((is_array($value) ? $value : [$value]) as $val) {
			foreach ((is_array($arg) ? $arg : [$arg]) as $item) {
				if ((string) $val === (string) $item) {
					continue 2;
				}
			}
			return false;
		}
		return true;
	}


	/**
	 * Is control's value not equal with second parameter?
	 * @return bool
	 */
	public static function validateNotEqual(IControl $control, $arg)
	{
		return !static::validateEqual($control, $arg);
	}


	/**
	 * Returns argument.
	 * @return bool
	 */
	public static function validateStatic(IControl $control, $arg)
	{
		return $arg;
	}


	/**
	 * Is control filled?
	 * @return bool
	 */
	public static function validateFilled(IControl $control)
	{
		return $control->isFilled();
	}


	/**
	 * Is control not filled?
	 * @return bool
	 */
	public static function validateBlank(IControl $control)
	{
		return !$control->isFilled();
	}


	/**
	 * Is control valid?
	 * @return bool
	 */
	public static function validateValid(Controls\BaseControl $control)
	{
		return $control->getRules()->validate();
	}


	/**
	 * Is a control's value number in specified range?
	 * @param  IControl
	 * @param  array
	 * @return bool
	 */
	public static function validateRange(IControl $control, $range)
	{
		$range = array_map(function ($v) {
			return $v === '' ? null : $v;
		}, $range);
		return Validators::isInRange($control->getValue(), $range);
	}


	/**
	 * Is a control's value number greater than or equal to the specified minimum?
	 * @param  IControl
	 * @param  float
	 * @return bool
	 */
	public static function validateMin(IControl $control, $minimum)
	{
		return Validators::isInRange($control->getValue(), [$minimum === '' ? null : $minimum, null]);
	}


	/**
	 * Is a control's value number less than or equal to the specified maximum?
	 * @param  IControl
	 * @param  float
	 * @return bool
	 */
	public static function validateMax(IControl $control, $maximum)
	{
		return Validators::isInRange($control->getValue(), [null, $maximum === '' ? null : $maximum]);
	}


	/**
	 * Count/length validator. Range is array, min and max length pair.
	 * @param  IControl
	 * @param  array|int
	 * @return bool
	 */
	public static function validateLength(IControl $control, $range)
	{
		if (!is_array($range)) {
			$range = [$range, $range];
		}
		$value = $control->getValue();
		return Validators::isInRange(is_array($value) ? count($value) : Strings::length((string) $value), $range);
	}


	/**
	 * Has control's value minimal count/length?
	 * @param  IControl
	 * @param  int
	 * @return bool
	 */
	public static function validateMinLength(IControl $control, $length)
	{
		return static::validateLength($control, [$length, null]);
	}


	/**
	 * Is control's value count/length in limit?
	 * @param  IControl
	 * @param  int
	 * @return bool
	 */
	public static function validateMaxLength(IControl $control, $length)
	{
		return static::validateLength($control, [null, $length]);
	}


	/**
	 * Has been button pressed?
	 * @return bool
	 */
	public static function validateSubmitted(Controls\SubmitButton $control)
	{
		return $control->isSubmittedBy();
	}


	/**
	 * Is control's value valid email address?
	 * @return bool
	 */
	public static function validateEmail(IControl $control)
	{
		return Validators::isEmail($control->getValue());
	}


	/**
	 * Is control's value valid URL?
	 * @return bool
	 */
	public static function validateUrl(IControl $control)
	{
		if (Validators::isUrl($value = $control->getValue())) {
			return true;

		} elseif (Validators::isUrl($value = "http://$value")) {
			$control->setValue($value);
			return true;
		}
		return false;
	}


	/**
	 * Matches control's value regular expression?
	 * @param  string
	 * @return bool
	 */
	public static function validatePattern(IControl $control, $pattern)
	{
		$value = $control->getValue() instanceof Nette\Http\FileUpload ? $control->getValue()->getName() : $control->getValue();
		return (bool) Strings::match($value, "\x01^(?:$pattern)\\z\x01u");
	}


	/**
	 * Is a control's value decimal number?
	 * @return bool
	 */
	public static function validateInteger(IControl $control)
	{
		if (Validators::isNumericInt($value = $control->getValue())) {
			if (!is_float($tmp = $value * 1)) { // bigint leave as string
				$control->setValue($tmp);
			}
			return true;
		}
		return false;
	}


	/**
	 * Is a control's value float number?
	 * @return bool
	 */
	public static function validateFloat(IControl $control)
	{
		$value = str_replace([' ', ','], ['', '.'], $control->getValue());
		if (Validators::isNumeric($value)) {
			$control->setValue((float) $value);
			return true;
		}
		return false;
	}


	/**
	 * Is file size in limit?
	 * @param  int
	 * @return bool
	 */
	public static function validateFileSize(Controls\UploadControl $control, $limit)
	{
		foreach (static::toArray($control->getValue()) as $file) {
			if ($file->getSize() > $limit || $file->getError() === UPLOAD_ERR_INI_SIZE) {
				return false;
			}
		}
		return true;
	}


	/**
	 * Has file specified mime type?
	 * @param  IControl
	 * @param  string|string[]
	 * @return bool
	 */
	public static function validateMimeType(Controls\UploadControl $control, $mimeType)
	{
		$mimeTypes = is_array($mimeType) ? $mimeType : explode(',', $mimeType);
		foreach (static::toArray($control->getValue()) as $file) {
			$type = strtolower($file->getContentType());
			if (!in_array($type, $mimeTypes, true) && !in_array(preg_replace('#/.*#', '/*', $type), $mimeTypes, true)) {
				return false;
			}
		}
		return true;
	}


	/**
	 * Is file image?
	 * @return bool
	 */
	public static function validateImage(Controls\UploadControl $control)
	{
		foreach (static::toArray($control->getValue()) as $file) {
			if (!$file->isImage()) {
				return false;
			}
		}
		return true;
	}


	/**
	 * @return array
	 */
	private static function toArray($value)
	{
		return $value instanceof Nette\Http\FileUpload ? [$value] : (array) $value;
	}
}
