<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use function is_array;
use const ENT_HTML5, ENT_QUOTES;


/**
 * Serializes validation rules into the JSON structure consumed by the
 * netteForms.js script via the data-nette-rules attribute.
 * @internal
 */
final class ClientRuleExporter
{
	use Nette\StaticClass;

	/**
	 * @return list<array<string, mixed>>
	 */
	public static function export(Rules $rules): array
	{
		return self::exportRuleList($rules)[0];
	}


	/**
	 * A rule that mutates the value stops the export of everything after it, including
	 * the rules following a condition whose branch contains such a rule, because the
	 * client cannot reproduce the server-side value. Server-only rules that do not
	 * mutate the value are simply skipped.
	 * @return array{list<array<string, mixed>>, bool}  [payload, export was stopped by a mutating rule]
	 */
	private static function exportRuleList(Rules $rules): array
	{
		$payload = [];
		foreach ($rules as $rule) {
			if (!$rule->canExport()) {
				if ($rule->mutatesValue()) {
					return [$payload, true];
				}

				continue;
			}

			// a rule without a validator object exports its operation and argument as-is
			$clientRule = $rule->getClientRule()
				?? new Validation\ClientRule(
					$rule->getOp() ?? $rule->getCallableName() ?? throw new Nette\InvalidStateException('Unknown validator type.'),
					$rule->arg,
				);

			[$item, $stopped] = self::exportRule($rule, $clientRule);
			if ($item) {
				$payload[] = $item;
			}

			if ($stopped) {
				return [$payload, true];
			}
		}

		return [$payload, false];
	}


	/**
	 * Exports one rule or condition given its client-side counterpart.
	 * @return array{?array<string, mixed>, bool}
	 */
	private static function exportRule(Rule $rule, Validation\ClientRule $clientRule): array
	{
		$item = ['op' => ($rule->isNegative ? '~' : '') . $clientRule->operation];
		$stopped = false;
		if ($rule->branch) {
			[$item['rules'], $stopped] = self::exportRuleList($rule->branch);
			$item['control'] = $rule->control->getHtmlName();
			if ($rule->branch->getToggles()) {
				$item['toggle'] = $rule->branch->getToggles();
			} elseif (!$item['rules']) {
				return [null, $stopped];
			}
		} else {
			$msg = MessageFormatter::format($rule, withValue: false, failure: $clientRule->message);
			$item['msg'] = $msg instanceof Nette\HtmlStringable
				? html_entity_decode(strip_tags((string) $msg), ENT_QUOTES | ENT_HTML5, 'UTF-8')
				: $msg;
		}

		self::exportArguments($item, $clientRule->arg, $rule->control);
		return [$item, $stopped];
	}


	/** @param  array<string, mixed>  $item */
	private static function exportArguments(array &$item, mixed $arg, Control $control): void
	{
		if (is_array($arg)) {
			$item['arg'] = [];
			foreach ($arg as $key => $value) {
				$item['arg'][$key] = self::exportArgument($value, $control);
			}
		} elseif ($arg !== null) {
			$item['arg'] = self::exportArgument($arg, $control);
		}
	}


	private static function exportArgument(mixed $value, Control $control): mixed
	{
		return match (true) {
			$value instanceof Control => ['control' => $value->getHtmlName()],
			$value === null => null,
			$control instanceof Controls\DateTimeControl => $control->formatHtmlValue($value),
			$value instanceof \DateTimeInterface => self::formatDateTime($value, $control),
			default => $value,
		};
	}


	/**
	 * Formats a date/time in the format of the value of the control's <input> type,
	 * so that the client compares it with the value as a string.
	 */
	public static function formatDateTime(\DateTimeInterface $value, Control $control): string
	{
		$type = $control instanceof Controls\TextInput ? $control->getControlPrototype()->type : null;
		$seconds = $value->format('s') !== '00';
		return $value->format(match ($type) {
			'date' => 'Y-m-d',
			'month' => 'Y-m',
			'week' => 'o-\WW',
			'time' => $seconds ? 'H:i:s' : 'H:i',
			'datetime-local' => $seconds ? 'Y-m-d\TH:i:s' : 'Y-m-d\TH:i',
			default => 'Y-m-d\TH:i:s',
		});
	}
}
