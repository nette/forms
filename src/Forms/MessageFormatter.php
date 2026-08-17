<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use function is_array, is_int, is_scalar, is_string;


/**
 * Renders validation failure messages: resolves the message source (validator
 * default, user override, app-wide override in Validator::$messages), translates
 * it and substitutes the placeholders.
 * @internal
 */
final class MessageFormatter
{
	use Nette\StaticClass;

	public static function format(
		Rule $rule,
		bool $withValue = true,
		?Validation\Message $failure = null,
	): string|Nette\HtmlStringable
	{
		if (
			$rule->validator instanceof Validation\Validator
			|| $rule->message instanceof Validation\Message
			|| ($failure !== null && $failure->id !== '')
		) {
			return self::formatValidationMessage($rule, $failure, $withValue);
		}

		$message = $rule->message;
		if ($message instanceof Nette\HtmlStringable) {
			return $message;

		} elseif ($message === null && is_string($rule->validator) && isset(Validator::$messages[$rule->validator])) {
			$message = Validator::$messages[$rule->validator];

		} elseif ($message == null) { // intentionally ==
			trigger_error(
				"Missing validation message for control '{$rule->control->getName()}'"
				. (is_string($rule->validator) ? " (validator '{$rule->validator}')." : '.'),
				E_USER_WARNING,
			);
		}

		$message ??= '';
		if ($translator = $rule->control->getForm()->getTranslator()) {
			$message = $translator->translate($message, is_int($rule->arg) ? $rule->arg : null);
		}

		return self::substitutePositional((string) $message, $rule, $withValue, $translator);
	}


	/**
	 * Renders the failure message of a validator object: resolves the user override,
	 * translates the message and substitutes the named parameters discovered by the
	 * validator, followed by the %name, %label and %value placeholders.
	 */
	private static function formatValidationMessage(
		Rule $rule,
		?Validation\Message $failure,
		bool $withValue,
	): string|Nette\HtmlStringable
	{
		if ($rule->message instanceof Nette\HtmlStringable) {
			return $rule->message;
		}

		$override = $rule->message;
		if ($override instanceof Validation\Message) {
			// a complete message provided instead of a plain text replaces the validator's failure
			$failure = $override;
			$override = null;
			$overrideKey = $failure->id;
		} else {
			$overrideKey = $rule->getOp(); // the legacy configuration layer is keyed by the operation
		}

		if (!$failure) {
			trigger_error("Missing validation message for control '{$rule->control->getName()}'.", E_USER_WARNING);
			$failure = new Validation\Message('', '');
		}

		$translator = $rule->control->getForm()->getTranslator();
		$template = self::resolveTemplate($failure, $override, $overrideKey, $translator);
		$message = $failure->substitute($template, fn(mixed $value): mixed => match (true) {
			$value instanceof Control => $withValue ? self::formatArgument($value, $value->getValue()) : null,
			$value instanceof \DateTimeInterface => self::formatArgument($rule->control, $value),
			default => $value,
		});

		return self::substitutePositional($message, $rule, $withValue, $translator);
	}


	/**
	 * Formats a message that is not attached to any rule (form-level errors such as
	 * repeater item counts or exceeded POST limit): resolves the app-wide override,
	 * translates the message and substitutes its named parameters.
	 */
	public static function formatStandalone(
		Validation\Message $message,
		?Nette\Localization\Translator $translator,
	): string
	{
		$template = self::resolveTemplate($message, null, $message->id, $translator);
		$result = $message->substitute($template);

		// %d in legacy override texts and translations keeps working
		if (str_contains($result, '%d')) {
			$fallback = $message->count;
			foreach ($message->parameters as $value) {
				if ($fallback !== null) {
					break;
				} elseif (is_scalar($value)) {
					$fallback = $value;
				}
			}

			if ($fallback !== null) {
				$result = str_replace('%d', (string) $fallback, $result);
			}
		}

		return $result;
	}


	/**
	 * Picks the message template: a user-supplied text beats the app-wide override
	 * in Validator::$messages (an entry changed against Validator::Defaults), which
	 * beats the validator's default; a translator receives whichever won.
	 */
	private static function resolveTemplate(
		Validation\Message $failure,
		string|\Stringable|null $override,
		?string $overrideKey,
		?Nette\Localization\Translator $translator,
	): string
	{
		if ($override === null && $overrideKey !== null) {
			$custom = Validator::$messages[$overrideKey] ?? null;
			if ($custom !== null && $custom !== (Validator::Defaults[$overrideKey] ?? null)) {
				$override = $custom; // app-wide override of the default message
			}
		}

		return (string) match (true) {
			$override !== null => $translator ? $translator->translate($override, $failure->count) : $override,
			$translator !== null => $translator->translate($failure, $failure->count),
			default => $failure->template,
		};
	}


	/**
	 * Substitutes the positional placeholders (%d, %s, %2$d consuming $rule->arg)
	 * and the %name, %label and %value placeholders.
	 */
	private static function substitutePositional(
		string $message,
		Rule $rule,
		bool $withValue,
		?Nette\Localization\Translator $translator,
	): string
	{
		return preg_replace_callback('#%(name|label|value|\d+\$[ds]|[ds])#', function (array $m) use ($rule, $withValue, $translator) {
			static $i = -1;
			switch ($m[1]) {
				case 'name': return $rule->control->getName() ?? '';
				case 'label': return self::formatLabel($rule, $translator);
				case 'value': return $withValue
					? self::formatArgument($rule->control, $rule->control->getValue())
					: $m[0];
				default:
					$args = is_array($rule->arg) ? $rule->arg : [$rule->arg];
					$i = (int) $m[1] ? (int) $m[1] - 1 : $i + 1;
					$arg = $args[$i] ?? null;
					return match (true) {
						$arg === null => '',
						$arg instanceof Control => $withValue ? self::formatArgument($arg, $arg->getValue()) : "%$i",
						$arg instanceof \BackedEnum => (string) $arg->value,
						default => self::formatArgument($rule->control, $arg),
					};
			}
		}, $message);
	}


	/**
	 * Formats a rule argument or a control value for a human-readable error message.
	 */
	private static function formatArgument(Control $control, mixed $value): string
	{
		return match (true) {
			$value === null => '',
			is_array($value) => implode(', ', array_map(fn($item) => self::formatArgument($control, $item), $value)),
			$value instanceof Nette\Http\FileUpload => $value->getUntrustedName(),
			$control instanceof Controls\DateTimeControl => $control->formatLocaleText($value),
			$value instanceof \DateTimeInterface => $value->format('Y-m-d H:i:s'),
			default => (string) $value,
		};
	}


	private static function formatLabel(Rule $rule, ?Nette\Localization\Translator $translator): string
	{
		// the caption is not part of the Control contract yet, see docs/todo
		$caption = $rule->control instanceof Controls\BaseControl ? $rule->control->getCaption() : null;
		$caption = match (true) {
			$caption === null => '',
			$caption instanceof Nette\Utils\Html => $caption->getText(),
			$caption instanceof Nette\HtmlStringable => (string) $caption,
			$translator !== null => $translator->translate($caption),
			default => $caption,
		};
		return rtrim((string) $caption, ':');
	}
}
