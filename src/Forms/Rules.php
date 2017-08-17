<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;


/**
 * List of validation & condition rules.
 */
class Rules implements \IteratorAggregate
{
	use Nette\SmartObject;

	/** @deprecated */
	public static $defaultMessages;

	/** @var Rule|false|null */
	private $required;

	/** @var Rule[] */
	private $rules = [];

	/** @var Rules */
	private $parent;

	/** @var array */
	private $toggles = [];

	/** @var IControl */
	private $control;


	public function __construct(IControl $control)
	{
		$this->control = $control;
	}


	/**
	 * Makes control mandatory.
	 * @param  mixed  state or error message
	 * @return static
	 */
	public function setRequired($value = true)
	{
		if ($value) {
			$this->addRule(Form::REQUIRED, $value === true ? null : $value);
		} else {
			$this->required = false;
		}
		return $this;
	}


	/**
	 * Is control mandatory?
	 * @return bool
	 */
	public function isRequired()
	{
		return (bool) $this->required;
	}


	/**
	 * @internal
	 */
	public function isOptional()
	{
		return $this->required === false;
	}


	/**
	 * Adds a validation rule for the current control.
	 * @param  mixed
	 * @param  string|object
	 * @param  mixed
	 * @return static
	 */
	public function addRule($validator, $errorMessage = null, $arg = null)
	{
		if ($validator === Form::VALID || $validator === ~Form::VALID) {
			throw new Nette\InvalidArgumentException('You cannot use Form::VALID in the addRule method.');
		}
		$rule = new Rule;
		$rule->control = $this->control;
		$rule->validator = $validator;
		$this->adjustOperation($rule);
		$rule->arg = $arg;
		$rule->message = $errorMessage;
		if ($rule->validator === Form::REQUIRED) {
			$this->required = $rule;
		} else {
			$this->rules[] = $rule;
		}
		return $this;
	}


	/**
	 * Adds a validation condition and returns new branch.
	 * @param  mixed
	 * @param  mixed
	 * @return static       new branch
	 */
	public function addCondition($validator, $arg = null)
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
	 * @param  IControl
	 * @param  mixed
	 * @param  mixed
	 * @return static     new branch
	 */
	public function addConditionOn(IControl $control, $validator, $arg = null)
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
		$rule->isNegative = !$rule->isNegative;
		$rule->branch = new static($this->parent->control);
		$rule->branch->parent = $this->parent;
		$this->parent->rules[] = $rule;
		return $rule->branch;
	}


	/**
	 * Ends current validation condition.
	 * @return Rules      parent branch
	 */
	public function endCondition()
	{
		return $this->parent;
	}


	/**
	 * Adds a filter callback.
	 * @param  callable
	 * @return static
	 */
	public function addFilter($filter)
	{
		Nette\Utils\Callback::check($filter);
		$this->rules[] = $rule = new Rule;
		$rule->control = $this->control;
		$rule->validator = function (IControl $control) use ($filter) {
			$control->setValue(call_user_func($filter, $control->getValue()));
			return true;
		};
		return $this;
	}


	/**
	 * Toggles HTML element visibility.
	 * @param  string
	 * @param  bool
	 * @return static
	 */
	public function toggle($id, $hide = true)
	{
		$this->toggles[$id] = $hide;
		return $this;
	}


	/**
	 * @param  bool
	 * @return array
	 */
	public function getToggles($actual = false)
	{
		return $actual ? $this->getToggleStates() : $this->toggles;
	}


	/**
	 * @internal
	 * @return array
	 */
	public function getToggleStates($toggles = [], $success = true)
	{
		foreach ($this->toggles as $id => $hide) {
			$toggles[$id] = ($success xor !$hide) || !empty($toggles[$id]);
		}

		foreach ($this->rules as $rule) {
			if ($rule->branch) {
				$toggles = $rule->branch->getToggleStates($toggles, $success && static::validateRule($rule));
			}
		}
		return $toggles;
	}


	/**
	 * Validates against ruleset.
	 * @return bool
	 */
	public function validate($emptyOptional = false)
	{
		$emptyOptional = $emptyOptional || $this->isOptional() && !$this->control->isFilled();
		foreach ($this as $rule) {
			if (!$rule->branch && $emptyOptional && $rule->validator !== Form::FILLED) {
				continue;
			}

			$success = $this->validateRule($rule);
			if ($success && $rule->branch && !$rule->branch->validate($rule->validator === Form::BLANK ? false : $emptyOptional)) {
				return false;

			} elseif (!$success && !$rule->branch) {
				$rule->control->addError(Validator::formatMessage($rule, true), false);
				return false;
			}
		}
		return true;
	}


	/**
	 * @internal
	 */
	public function check()
	{
		if ($this->required !== null) {
			return;
		}
		foreach ($this->rules as $rule) {
			if ($rule->control === $this->control && ($rule->validator === Form::FILLED || $rule->validator === Form::BLANK)) {
				// ignore
			} elseif ($rule->branch) {
				if ($rule->branch->check() === true) {
					return true;
				}
			} else {
				trigger_error("Missing setRequired(true | false) on field '{$rule->control->getName()}' in form '{$rule->control->getForm()->getName()}'.", E_USER_WARNING);
				return true;
			}
		}
	}


	/**
	 * Validates single rule.
	 * @return bool
	 */
	public static function validateRule(Rule $rule)
	{
		$args = is_array($rule->arg) ? $rule->arg : [$rule->arg];
		foreach ($args as &$val) {
			$val = $val instanceof IControl ? $val->getValue() : $val;
		}
		return $rule->isNegative
			xor call_user_func(self::getCallback($rule), $rule->control, is_array($rule->arg) ? $args : $args[0]);
	}


	/**
	 * Iterates over complete ruleset.
	 * @return \Iterator
	 */
	public function getIterator()
	{
		$rules = $this->rules;
		if ($this->required) {
			array_unshift($rules, $this->required);
		}
		return new \ArrayIterator($rules);
	}


	/**
	 * Process 'operation' string.
	 * @return void
	 */
	private function adjustOperation(Rule $rule)
	{
		if (is_string($rule->validator) && ord($rule->validator[0]) > 127) {
			$rule->isNegative = true;
			$rule->validator = ~$rule->validator;
			if (!$rule->branch) {
				$name = strncmp($rule->validator, ':', 1) ? $rule->validator : 'Form:' . strtoupper($rule->validator);
				trigger_error("Negative validation rules such as ~$name are deprecated.", E_USER_DEPRECATED);
			}
			if ($rule->validator === Form::FILLED) {
				$rule->validator = Form::BLANK;
				$rule->isNegative = false;
				trigger_error('Replace negative validation rule ~Form::FILLED with Form::BLANK.', E_USER_DEPRECATED);
			}
		}

		if (!is_callable($this->getCallback($rule))) {
			$validator = is_scalar($rule->validator) ? " '$rule->validator'" : '';
			throw new Nette\InvalidArgumentException("Unknown validator$validator for control '{$rule->control->name}'.");
		}
	}


	private static function getCallback(Rule $rule)
	{
		$op = $rule->validator;
		if (is_string($op) && strncmp($op, ':', 1) === 0) {
			return 'Nette\Forms\Validator::validate' . ltrim($op, ':');
		} else {
			return $op;
		}
	}
}

Rules::$defaultMessages = &Validator::$messages;
