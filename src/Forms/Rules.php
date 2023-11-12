<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms;

use Nette;


/**
 * List of validation & condition rules.
 * @implements \IteratorAggregate<int, Rule>
 */
class Rules implements \IteratorAggregate
{
	private const NegRules = [
		Form::Filled => Form::Blank,
		Form::Blank => Form::Filled,
	];

	/** @var Rule|null */
	private $required;

	/** @var Rule[] */
	private $rules = [];

	/** @var Rules */
	private $parent;

	/** @var array */
	private $toggles = [];

	/** @var Control */
	private $control;


	public function __construct(Control $control)
	{
		$this->control = $control;
	}


	/**
	 * Makes control mandatory.
	 * @param  string|bool  $value
	 * @return static
	 */
	public function setRequired($value = true)
	{
		if ($value) {
			$this->addRule(Form::Filled, $value === true ? null : $value);
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
	 * @param  callable|string  $validator
	 * @param  string|object  $errorMessage
	 * @return static
	 */
	public function addRule($validator, $errorMessage = null, $arg = null)
	{
		if ($validator === Form::Valid || $validator === ~Form::Valid) {
			throw new Nette\InvalidArgumentException('You cannot use Form::Valid in the addRule method.');
		}

		$rule = new Rule;
		$rule->control = $this->control;
		$rule->validator = $validator;
		$rule->arg = $arg;
		$rule->message = $errorMessage;
		$this->adjustOperation($rule);
		if ($rule->validator === Form::Filled) {
			$this->required = $rule;
		} else {
			$this->rules[] = $rule;
		}

		return $this;
	}


	/**
	 * Removes a validation rule for the current control.
	 * @param  callable|string  $validator
	 * @return static
	 */
	public function removeRule($validator)
	{
		if ($validator === Form::Filled) {
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
	 * @return static       new branch
	 */
	public function addCondition($validator, $arg = null)
	{
		if ($validator === Form::Valid || $validator === ~Form::Valid) {
			throw new Nette\InvalidArgumentException('You cannot use Form::Valid in the addCondition method.');
		} elseif (is_bool($validator)) {
			$arg = $validator;
			$validator = ':static';
		}

		return $this->addConditionOn($this->control, $validator, $arg);
	}


	/**
	 * Adds a validation condition on specified control a returns new branch.
	 * @return static     new branch
	 */
	public function addConditionOn(Control $control, $validator, $arg = null)
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
	 * @return static    else branch
	 */
	public function elseCondition()
	{
		$rule = clone end($this->parent->rules);
		if (isset(self::NegRules[$rule->validator])) {
			$rule->validator = self::NegRules[$rule->validator];
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
	 * @return Rules      parent branch
	 */
	public function endCondition(): self
	{
		return $this->parent;
	}


	/**
	 * Adds a filter callback.
	 * @return static
	 */
	public function addFilter(callable $filter)
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
	 * @return static
	 */
	public function toggle(string $id, bool $hide = true)
	{
		$this->toggles[$id] = $hide;
		return $this;
	}


	public function getToggles(bool $actual = false): array
	{
		return $actual ? $this->getToggleStates() : $this->toggles;
	}


	/** @internal */
	public function getToggleStates(array $toggles = [], bool $success = true, ?bool $emptyOptional = null): array
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
					$rule->validator === Form::Blank ? false : $emptyOptional,
				);
			} elseif (!$emptyOptional || $rule->validator === Form::Filled) {
				$success = $success && static::validateRule($rule);
			}
		}

		return $toggles;
	}


	/**
	 * Validates against ruleset.
	 */
	public function validate(?bool $emptyOptional = null): bool
	{
		$emptyOptional ??= (!$this->isRequired() && !$this->control->isFilled());
		foreach ($this as $rule) {
			if (!$rule->branch && $emptyOptional && $rule->validator !== Form::Filled) {
				continue;
			}

			$success = $this->validateRule($rule);
			if (
				$success
				&& $rule->branch
				&& !$rule->branch->validate($rule->validator === Form::Blank ? false : $emptyOptional)
			) {
				return false;

			} elseif (!$success && !$rule->branch) {
				$rule->control->addError(Validator::formatMessage($rule), translate: false);
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
	 * @return \ArrayIterator<int, Rule>
	 */
	public function getIterator(): \Iterator
	{
		$priorities = [
			0 => [], // Blank
			1 => $this->required ? [$this->required] : [],
			2 => [], // other rules
		];
		foreach ($this->rules as $rule) {
			$priorities[$rule->validator === Form::Blank && $rule->control === $this->control ? 0 : 2][] = $rule;
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

			if (isset(self::NegRules[$rule->validator])) {
				$rule->validator = self::NegRules[$rule->validator];
				$rule->isNegative = false;
				trigger_error('Replace negative validation rule ~Form::Filled with Form::Blank and vice versa.', E_USER_DEPRECATED);
			}
		}

		if ($rule->validator === Form::Image) {
			$rule->arg = Helpers::getSupportedImages();
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
