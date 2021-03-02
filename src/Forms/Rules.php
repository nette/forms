<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms;

use Nette;
use Stringable;


/**
 * List of validation & condition rules.
 */
class Rules implements \IteratorAggregate
{
	use Nette\SmartObject;

	private const NEG_RULES = [
		Form::FILLED => Form::BLANK,
		Form::BLANK => Form::FILLED,
	];

	private ?Rule $required = null;

	/** @var Rule[] */
	private array $rules = [];

	private Rules $parent;

	private array $toggles = [];

	private Control $control;


	public function __construct(Control $control)
	{
		$this->control = $control;
	}


	/**
	 * Makes control mandatory.
	 */
	public function setRequired(string|Stringable|bool $value = true): static
	{
		if ($value) {
			$this->addRule(Form::FILLED, $value === true ? null : $value);
		} else {
			$this->required = null;
		}
		return $this;
	}


	/**
	 * Is control mandatory?
	 */
	public function isRequired(): bool
	{
		return (bool) $this->required;
	}


	/**
	 * Adds a validation rule for the current control.
	 */
	public function addRule(
		callable|string $validator,
		string|Stringable $errorMessage = null,
		mixed $arg = null,
	): static {
		if ($validator === Form::VALID || $validator === ~Form::VALID) {
			throw new Nette\InvalidArgumentException('You cannot use Form::VALID in the addRule method.');
		}
		$rule = new Rule;
		$rule->control = $this->control;
		$rule->validator = $validator;
		$this->adjustOperation($rule);
		$rule->arg = $arg;
		$rule->message = $errorMessage;
		if ($rule->validator === Form::FILLED) {
			$this->required = $rule;
		} else {
			$this->rules[] = $rule;
		}
		return $this;
	}


	/**
	 * Removes a validation rule for the current control.
	 */
	public function removeRule(callable|string $validator): static
	{
		if ($validator === Form::FILLED) {
			$this->required = null;
		} else {
			foreach ($this->rules as $i => $rule) {
				if (!$rule->branch && $rule->validator === $validator) {
					unset($this->rules[$i]);
				}
			}
		}
		return $this;
	}


	/**
	 * Adds a validation condition and returns new branch.
	 */
	public function addCondition($validator, $arg = null): static
	{
		if ($validator === Form::VALID || $validator === ~Form::VALID) {
			throw new Nette\InvalidArgumentException('You cannot use Form::VALID in the addCondition method.');
		} elseif (is_bool($validator)) {
			$arg = $validator;
			$validator = ':static';
		}
		return $this->addConditionOn($this->control, $validator, $arg);
	}


	/**
	 * Adds a validation condition on specified control a returns new branch.
	 */
	public function addConditionOn(Control $control, $validator, $arg = null): static
	{
		$rule = new Rule;
		$rule->control = $control;
		$rule->validator = $validator;
		$rule->arg = $arg;
		$rule->branch = new static($this->control);
		$rule->branch->parent = $this;
		$this->adjustOperation($rule);

		$this->rules[] = $rule;
		return $rule->branch;
	}


	/**
	 * Adds a else statement.
	 */
	public function elseCondition(): static
	{
		$rule = clone end($this->parent->rules);
		if (isset(self::NEG_RULES[$rule->validator])) {
			$rule->validator = self::NEG_RULES[$rule->validator];
		} else {
			$rule->isNegative = !$rule->isNegative;
		}
		$rule->branch = new static($this->parent->control);
		$rule->branch->parent = $this->parent;
		$this->parent->rules[] = $rule;
		return $rule->branch;
	}


	/**
	 * Ends current validation condition.
	 */
	public function endCondition(): static
	{
		return $this->parent;
	}


	/**
	 * Adds a filter callback.
	 */
	public function addFilter(callable $filter): static
	{
		$this->rules[] = $rule = new Rule;
		$rule->control = $this->control;
		$rule->validator = function (Control $control) use ($filter): bool {
			$control->setValue($filter($control->getValue()));
			return true;
		};
		return $this;
	}


	/**
	 * Toggles HTML element visibility.
	 */
	public function toggle(string $id, bool $hide = true): static
	{
		$this->toggles[$id] = $hide;
		return $this;
	}


	public function getToggles(bool $actual = false): array
	{
		return $actual ? $this->getToggleStates() : $this->toggles;
	}


	/** @internal */
	public function getToggleStates(array $toggles = [], bool $success = true, bool $emptyOptional = null): array
	{
		foreach ($this->toggles as $id => $hide) {
			$toggles[$id] = ($success xor !$hide) || !empty($toggles[$id]);
		}

		$emptyOptional ??= (!$this->isRequired() && !$this->control->isFilled());
		foreach ($this as $rule) {
			if ($rule->branch) {
				$toggles = $rule->branch->getToggleStates(
					$toggles,
					$success && static::validateRule($rule),
					$rule->validator === Form::BLANK ? false : $emptyOptional,
				);
			} elseif (!$emptyOptional || $rule->validator === Form::FILLED) {
				$success = $success && static::validateRule($rule);
			}
		}
		return $toggles;
	}


	/**
	 * Validates against ruleset.
	 */
	public function validate(bool $emptyOptional = null): bool
	{
		$emptyOptional ??= (!$this->isRequired() && !$this->control->isFilled());
		foreach ($this as $rule) {
			if (!$rule->branch && $emptyOptional && $rule->validator !== Form::FILLED) {
				continue;
			}

			$success = $this->validateRule($rule);
			if (
				$success
				&& $rule->branch
				&& !$rule->branch->validate($rule->validator === Form::BLANK ? false : $emptyOptional)
			) {
				return false;

			} elseif (!$success && !$rule->branch) {
				$rule->control->addError(Validator::formatMessage($rule, true), false);
				return false;
			}
		}
		return true;
	}


	/**
	 * Clear all validation rules.
	 */
	public function reset(): void
	{
		$this->rules = [];
	}


	/**
	 * Validates single rule.
	 */
	public static function validateRule(Rule $rule): bool
	{
		$args = is_array($rule->arg) ? $rule->arg : [$rule->arg];
		foreach ($args as &$val) {
			$val = $val instanceof Control ? $val->getValue() : $val;
		}
		return $rule->isNegative
			xor self::getCallback($rule)($rule->control, is_array($rule->arg) ? $args : $args[0]);
	}


	/**
	 * Iterates over complete ruleset.
	 */
	public function getIterator(): \Iterator
	{
		$priorities = [
			0 => [], // BLANK
			1 => $this->required ? [$this->required] : [],
			2 => [], // other rules
		];
		foreach ($this->rules as $rule) {
			$priorities[$rule->validator === Form::BLANK && $rule->control === $this->control ? 0 : 2][] = $rule;
		}
		return new \ArrayIterator(array_merge(...$priorities));
	}


	/**
	 * Process 'operation' string.
	 */
	private function adjustOperation(Rule $rule): void
	{
		if (is_string($rule->validator) && ord($rule->validator[0]) > 127) {
			$rule->isNegative = true;
			$rule->validator = ~$rule->validator;
			if (!$rule->branch) {
				$name = strncmp($rule->validator, ':', 1)
					? $rule->validator
					: 'Form:' . strtoupper($rule->validator);
				trigger_error("Negative validation rules such as ~$name are deprecated.", E_USER_DEPRECATED);
			}
			if (isset(self::NEG_RULES[$rule->validator])) {
				$rule->validator = self::NEG_RULES[$rule->validator];
				$rule->isNegative = false;
				trigger_error('Replace negative validation rule ~Form::FILLED with Form::BLANK and vice versa.', E_USER_DEPRECATED);
			}
		}

		if (!is_callable($this->getCallback($rule))) {
			$validator = is_scalar($rule->validator)
				? " '$rule->validator'"
				: '';
			throw new Nette\InvalidArgumentException("Unknown validator$validator for control '{$rule->control->name}'.");
		}
	}


	private static function getCallback(Rule $rule)
	{
		$op = $rule->validator;
		return is_string($op) && strncmp($op, ':', 1) === 0
			? [Validator::class, 'validate' . ltrim($op, ':')]
			: $op;
	}
}
