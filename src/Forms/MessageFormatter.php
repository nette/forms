<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use function in_array, is_array, is_int, is_scalar;


/**
 * Renders validation messages. The template is the message given to the rule, otherwise
 * an app-wide override in Validator::$messages (keyed by the message identifier or by the
 * operation it belongs to), otherwise the validator's default. The translator receives
 * the Message object with the chosen template and the parameters are substituted after
 * the translation.
 */
final class MessageFormatter
{
	use Nette\StaticClass;

	/**
	 * Renders the failure message of a rule; $failure is the message reported by its validator.
	 */
	public static function format(
		Rule $rule,
		bool $withValue = true,
		?Validation\Message $failure = null,
	): string|Nette\HtmlStringable
	{
		$text = $rule->message;
		if ($text instanceof Nette\HtmlStringable) {
			return $text;
		} elseif ($text instanceof Validation\Message) {
			$failure = $text;
			$text = null;
		}

		$translator = $rule->control->getForm()->getTranslator();
		if ($text !== null) {
			$text = $translator
				? $translator->translate($text, $failure->count ?? (is_int($rule->arg) ? $rule->arg : null))
				: $text;
		} elseif ($failure !== null) {
			$text = self::translate($failure, $translator);
		} else {
			trigger_error(
				"Missing validation message for control '{$rule->control->getName()}'"
				. (($op = $rule->getOp()) ? " (validator '$op')." : '.'),
				E_USER_WARNING,
			);
			$text = '';
		}

		$coerce = function (mixed $value) use (&$coerce, $rule, $withValue): mixed {
			return match (true) {
				is_array($value) => implode(', ', array_map($coerce, $value)),
				$value instanceof Control => $withValue ? self::formatArgument($value, $value->getValue()) : null,
				$value instanceof \DateTimeInterface => self::formatArgument($rule->control, $value),
				$value instanceof \BackedEnum => $value->value,
				default => $value,
			};
		};
		$text = $failure ? $failure->substitute($text, $coerce) : (string) $text;
		$args = match (true) {
			$rule->arg !== null => is_array($rule->arg) ? $rule->arg : [$rule->arg],
			$failure !== null => self::getPositionalArguments($failure),
			default => [],
		};
		return self::substitutePositional($text, $args, $rule, $withValue, $translator);
	}


	/**
	 * Formats a message that is not attached to any rule (form-level errors such as
	 * repeater item counts or exceeded POST limit).
	 */
	public static function formatStandalone(
		Validation\Message $message,
		?Nette\Localization\Translator $translator,
	): string
	{
		$text = $message->substitute(self::translate($message, $translator));
		return self::substitutePositional($text, self::getPositionalArguments($message));
	}


	/**
	 * Applies the app-wide override of the message and translates it.
	 */
	private static function translate(Validation\Message $message, ?Nette\Localization\Translator $translator): string
	{
		$override = Validator::$messages[$message->id]
			?? Validator::$messages[Validation\Operations::getMessageOperation($message->id) ?? '']
			?? null;
		if ($override !== null) {
			$message = $message->withTemplate($override);
		}

		return (string) ($translator ? $translator->translate($message, $message->count) : $message);
	}


	/**
	 * Values consumed by the positional placeholders %d and %s of texts written for operations.
	 * @return list<mixed>
	 */
	private static function getPositionalArguments(Validation\Message $message): array
	{
		return $message->count !== null
			? [$message->count]
			: array_values($message->parameters);
	}


	/**
	 * Substitutes the positional placeholders (%d, %s, %2$d) and, given a rule,
	 * the %name, %label and %value placeholders.
	 * @param  mixed[]  $args
	 */
	private static function substitutePositional(
		string $message,
		array $args,
		?Rule $rule = null,
		bool $withValue = true,
		?Nette\Localization\Translator $translator = null,
	): string
	{
		$i = -1;
		return preg_replace_callback(
			'#%(name|label|value|\d+\$[ds]|[ds])#',
			function (array $m) use ($args, $rule, $withValue, $translator, &$i) {
				if ($rule === null && in_array($m[1], ['name', 'label', 'value'], true)) {
					return $m[0];
				}

				switch ($m[1]) {
					case 'name': return $rule->control->getName() ?? '';
					case 'label': return self::formatLabel($rule, $translator);
					case 'value': return $withValue
						? self::formatArgument($rule->control, $rule->control->getValue())
						: $m[0];
					default:
						$i = (int) $m[1] ? (int) $m[1] - 1 : $i + 1;
						$arg = $args[$i] ?? null;
						return match (true) {
							$arg === null => '',
							$arg instanceof Control => $withValue ? self::formatArgument($arg, $arg->getValue()) : "%$i",
							$arg instanceof \BackedEnum => (string) $arg->value,
							$rule === null => is_scalar($arg) ? (string) $arg : '',
							default => self::formatArgument($rule->control, $arg),
						};
				}
			},
			$message,
		);
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
			$value instanceof \DateTimeInterface => str_replace('T', ' ', ClientRuleExporter::formatDateTime($value, $control)),
			default => (string) $value,
		};
	}


	private static function formatLabel(Rule $rule, ?Nette\Localization\Translator $translator): string
	{
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
