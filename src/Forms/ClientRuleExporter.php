<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use function count, is_array;
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

			$clientRules = $rule->getClientRules();
			if (!$clientRules) {
				// a rule without an object equivalent exports its operation and argument as-is
				$operation = $rule->getOp()
					?? $rule->getCallableName()
					?? throw new Nette\InvalidStateException('Unknown validator type.');
				$clientRules = [new Validation\ClientRule($operation, $rule->arg)];
			}

			[$items, $stopped] = self::exportRule($rule, $clientRules);
			foreach ($items as $item) {
				$payload[] = $item;
			}

			if ($stopped) {
				return [$payload, true];
			}
		}

		return [$payload, false];
	}


	/**
	 * Exports one rule given its client-side counterparts. A single rule may translate
	 * to several client-side items, each with its own message. A condition is
	 * exportable only when it translates to exactly one client rule.
	 * @param  list<Validation\ClientRule>  $clientRules
	 * @return array{list<array<string, mixed>>, bool}
	 */
	private static function exportRule(Rule $rule, array $clientRules): array
	{
		$negative = $rule->isNegative ? '~' : '';
		if ($rule->branch) {
			if (count($clientRules) !== 1) {
				// a condition needs a single operation, so a split one (Length with both bounds) is exported as written
				if ($rule->getOp() === null) {
					return [[], false];
				}

				$clientRules = [new Validation\ClientRule($rule->getOp(), $rule->arg)];
			}

			[$branchRules, $stopped] = self::exportRuleList($rule->branch);
			$item = [
				'op' => $negative . $clientRules[0]->operation,
				'rules' => $branchRules,
				'control' => $rule->control->getHtmlName(),
			];
			if ($rule->branch->getToggles()) {
				$item['toggle'] = $rule->branch->getToggles();
			} elseif (!$item['rules']) {
				return [[], $stopped];
			}

			self::exportArguments($item, $clientRules[0]->arg, $rule->control);
			return [[$item], $stopped];
		}

		$items = [];
		foreach ($clientRules as $clientRule) {
			$msg = MessageFormatter::format($rule, withValue: false, failure: $clientRule->message);
			if ($msg instanceof Nette\HtmlStringable) {
				$msg = html_entity_decode(strip_tags((string) $msg), ENT_QUOTES | ENT_HTML5, 'UTF-8');
			}

			$item = ['op' => $negative . $clientRule->operation, 'msg' => $msg];
			self::exportArguments($item, $clientRule->arg, $rule->control);
			$items[] = $item;
		}

		return [$items, false];
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
		if ($value instanceof Control) {
			return ['control' => $value->getHtmlName()];
		} elseif ($control instanceof Controls\DateTimeControl) {
			return $control->formatHtmlValue($value);
		} elseif ($value instanceof \DateTimeInterface) {
			return $value->format('Y-m-d\TH:i:s');
		} else {
			return $value;
		}
	}
}
