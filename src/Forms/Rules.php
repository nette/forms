<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use Stringable;
use function is_array, is_bool, is_scalar, is_string;


/**
 * Manages validation rules and conditions for a single form control.
 * @implements \IteratorAggregate<int, Rule>
 */
final class Rules implements \IteratorAggregate
{
	private const NegRules = [
		Form::Filled => Form::Blank,
		Form::Blank => Form::Filled,
	];

	private ?Rule $required = null;

	/** @var Rule[] */
	private array $rules = [];
	private Rules $parent;

	/** @var array<string, bool> */
	private array $toggles = [];


	public function __construct(
		private readonly Control $control,
	) {
	}


	/**
	 * Makes control mandatory.
	 */
	public function setRequired(string|Stringable|bool $value = true): static
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
	 * @param  Validation\Validator|(callable(never, never): bool)|string  $validator
	 */
	public function addRule(
		Validation\Validator|callable|string $validator,
		string|Stringable|null $errorMessage = null,
		mixed $arg = null,
	): static
	{
		if ($validator === Form::Valid || $validator === ~Form::Valid) {
			throw new Nette\InvalidArgumentException('You cannot use Form::Valid in the addRule method.');
		}

		self::checkValidatorArg($validator, $arg);
		[$validator, $isNegative] = $this->normalizeValidator($validator, $this->control, isCondition: false);
		$rule = new Rule($this->control, $validator, $arg, $errorMessage, $isNegative);
		if ($rule->checksFilled()) {
			$this->required = $rule;
		} else {
			$this->rules[] = $rule;
		}

		return $this;
	}


	/**
	 * Removes a validation rule for the current control. Rules with validator objects
	 * are removed by passing the object's class name.
	 * @param  Validation\Validator|(callable(never, never): bool)|string  $validator
	 */
	public function removeRule(Validation\Validator|callable|string $validator): static
	{
		$class = match (true) {
			$validator instanceof Validation\Validator => $validator::class,
			is_string($validator) && is_a($validator, Validation\Validator::class, allow_string: true) => $validator,
			default => null,
		};

		if ($validator === Form::Filled || ($class !== null && $this->required?->validator instanceof $class)) {
			$this->required = null;
		} else {
			foreach ($this->rules as $i => $rule) {
				if (!$rule->branch && ($class !== null ? $rule->validator instanceof $class : $rule->validator === $validator)) {
					unset($this->rules[$i]);
				}
			}
		}

		return $this;
	}


	/**
	 * Adds a validation condition and returns new branch.
	 * @param  Validation\Validator|(callable(never, never): bool)|string|bool  $validator
	 */
	public function addCondition(
		Validation\Validator|callable|string|bool $validator,
		mixed $arg = null,
	): static
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
	 * Adds a validation condition on a specified control and returns new branch.
	 * @param  Validation\Validator|(callable(never, never): bool)|string  $validator
	 */
	public function addConditionOn(
		Control $control,
		Validation\Validator|callable|string $validator,
		mixed $arg = null,
	): static
	{
		self::checkValidatorArg($validator, $arg);
		[$validator, $isNegative] = $this->normalizeValidator($validator, $control, isCondition: true);

		$branch = new static($this->control);
		$branch->parent = $this;
		$this->rules[] = new Rule($control, $validator, $arg, isNegative: $isNegative, branch: $branch);
		return $branch;
	}


	/**
	 * Adds an else branch to the current condition and returns it.
	 */
	public function elseCondition(): static
	{
		assert($this->parent->rules !== []);
		$last = end($this->parent->rules);
		$branch = new static($this->parent->control);
		$branch->parent = $this->parent;
		$negable = is_string($last->validator) ? (self::NegRules[$last->validator] ?? null) : null;
		$this->parent->rules[] = new Rule(
			$last->control,
			$negable ?? $last->validator,
			$last->arg,
			$last->message,
			$negable === null ? !$last->isNegative : $last->isNegative,
			$branch,
		);
		return $branch;
	}


	/**
	 * Ends current validation condition.
	 */
	public function endCondition(): static
	{
		return $this->parent;
	}


	/**
	 * Adds a value filter applied before validation.
	 * @param callable(mixed): mixed  $filter
	 */
	public function addFilter(callable $filter): static
	{
		$this->rules[] = new Rule($this->control, new Validation\CallbackFilter($filter(...)));
		return $this;
	}


	/**
	 * Shows or hides an HTML element (selected by CSS selector) when the condition is met.
	 */
	public function toggle(string $id, bool $hide = true): static
	{
		$this->toggles[$id] = $hide;
		return $this;
	}


	/**
	 * Returns toggle definitions, or current evaluated states when $actual is true.
	 * @return array<string, bool>
	 */
	public function getToggles(bool $actual = false): array
	{
		return $actual ? $this->getToggleStates() : $this->toggles;
	}


	/**
	 * @internal
	 * @param  array<string, bool>  $toggles
	 * @return array<string, bool>
	 */
	public function getToggleStates(array $toggles = [], bool $success = true, ?bool $emptyOptional = null): array
	{
		foreach ($this->toggles as $id => $hide) {
			$toggles[$id] = ($success xor !$hide) || !empty($toggles[$id]);
		}

		$emptyOptional ??= $this->isEmptyOptional();
		// filters must not run here: computing toggle states cannot modify control values
		foreach ($this as $rule) {
			if ($rule->branch) {
				$toggles = $rule->branch->getToggleStates(
					$toggles,
					$success && self::evaluateRule($rule, applyFilters: false) === null,
					$rule->checksBlank() ? false : $emptyOptional,
				);
			} elseif (!self::shouldSkip($rule, $emptyOptional)) {
				$success = $success && self::evaluateRule($rule, applyFilters: false) === null;
			}
		}

		return $toggles;
	}


	/**
	 * Validates the control against all rules. Returns false and sets an error message on failure.
	 */
	public function validate(?bool $emptyOptional = null): bool
	{
		$emptyOptional ??= $this->isEmptyOptional();
		foreach ($this as $rule) {
			if (self::shouldSkip($rule, $emptyOptional)) {
				continue;
			}

			$failure = self::evaluateRule($rule);
			if (
				!$failure
				&& $rule->branch
				&& !$rule->branch->validate($rule->checksBlank() ? false : $emptyOptional)
			) {
				return false;

			} elseif ($failure && !$rule->branch) {
				$rule->control->addError(MessageFormatter::format($rule, failure: $failure), translate: false);
				return false;
			}
		}

		return true;
	}


	/**
	 * An empty optional control skips all value rules; only the requiredness check remains relevant.
	 */
	private static function shouldSkip(Rule $rule, bool $emptyOptional): bool
	{
		return !$rule->branch
			&& $emptyOptional
			&& !$rule->checksFilled();
	}


	/**
	 * An optional control left empty by the user; its value rules are not evaluated.
	 */
	private function isEmptyOptional(): bool
	{
		return !$this->isRequired() && !$this->control->isFilled();
	}


	/**
	 * Removes all validation rules.
	 */
	public function reset(): void
	{
		$this->rules = [];
	}


	/**
	 * Does any rule at any depth change the value on the server,
	 * so the raw input may differ from the validated value?
	 * @internal
	 */
	public function containsMutating(): bool
	{
		foreach ($this->rules as $rule) {
			if ($rule->mutatesValue() || $rule->branch?->containsMutating()) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Validator objects carry their configuration themselves.
	 */
	private static function checkValidatorArg(mixed $validator, mixed $arg): void
	{
		if ($validator instanceof Validation\Validator && $arg !== null) {
			throw new Nette\InvalidArgumentException('Validator objects carry their configuration themselves, the $arg argument must be null.');
		}
	}


	/**
	 * Validates single rule.
	 */
	public static function validateRule(Rule $rule): bool
	{
		return self::evaluateRule($rule) === null;
	}


	/**
	 * Single evaluation point for all validator kinds. Returns null on success,
	 * or a message describing the failure. Negation drops the message, which is
	 * fine because negative rules exist only as conditions and those discard it.
	 * @internal
	 */
	public static function evaluateRule(Rule $rule, bool $applyFilters = true): ?Validation\Message
	{
		$validator = $rule->getValidationObject();
		if ($validator instanceof Validation\FilledValidator || $validator instanceof Validation\BlankValidator) {
			// filledness is a control state, not a value property; the engine evaluates these two directly
			$failure = $validator->validateControl($rule->control);

		} elseif ($validator instanceof Validation\ValueValidator) {
			$value = $rule->control->getValue();
			$failure = $validator->validate($value);
			if ($failure === null && $applyFilters && !$rule->isNegative && $validator instanceof Validation\Filter) {
				$rule->control->setValue($validator->filter($value));
			}
		} elseif ($rule->validator instanceof Validation\Filter) {
			if ($applyFilters) {
				$rule->control->setValue($rule->validator->filter($rule->control->getValue()));
			}

			$failure = null;

		} elseif (is_string($raw = $rule->validator) || is_callable($raw)) {
			$args = is_array($rule->arg) ? $rule->arg : [$rule->arg];
			foreach ($args as &$val) {
				$val = $val instanceof Control ? $val->getValue() : $val;
			}

			$callback = self::getCallback($raw);
			assert(is_callable($callback));
			$failure = $callback($rule->control, is_array($rule->arg) ? $args : $args[0])
				? null
				: self::createMessage();

		} else {
			throw new Nette\InvalidStateException('Unknown validator type.');
		}

		if ($rule->isNegative) {
			$failure = $failure === null ? self::createMessage() : null;
		}

		return $failure;
	}


	/**
	 * Creates the failure marker for validators that report only success/failure without an own message.
	 */
	private static function createMessage(): Validation\Message
	{
		return new Validation\Message('', '');
	}


	/**
	 * Iterates over all rules in priority order (Blank first, then Required, then others).
	 * @return \Iterator<int, Rule>
	 */
	public function getIterator(): \Iterator
	{
		$priorities = [
			0 => [], // Blank
			1 => $this->required ? [$this->required] : [],
			2 => [], // other rules
		];
		foreach ($this->rules as $rule) {
			$priorities[$rule->checksBlank() && $rule->control === $this->control ? 0 : 2][] = $rule;
		}

		return new \ArrayIterator(array_merge(...$priorities));
	}


	/**
	 * Normalizes the validator identifier (parses the ~ negation prefix) and
	 * verifies that a callable exists. Runs before the Rule is constructed.
	 * @return array{mixed, bool}  [validator, isNegative]
	 */
	private function normalizeValidator(mixed $validator, Control $control, bool $isCondition): array
	{
		$isNegative = false;
		if (is_string($validator) && ord($validator[0]) > 127) {
			$isNegative = true;
			$validator = ~$validator;
			if (!$isCondition) {
				$name = strncmp($validator, ':', 1)
					? $validator
					: 'Form:' . strtoupper($validator);
				throw new Nette\InvalidArgumentException("Negative validation rules such as ~$name are deprecated.");
			}
		}

		if (
			!$validator instanceof Validation\Validator
			&& (is_string($validator) || is_callable($validator))
			&& !is_callable(self::getCallback($validator))
		) {
			$name = is_scalar($validator)
				? " '$validator'"
				: '';
			throw new Nette\InvalidArgumentException("Unknown validator$name for control '{$control->getName()}'.");
		}

		return [$validator, $isNegative];
	}


	/** @param  (callable(never, never): bool)|string  $validator */
	private static function getCallback(callable|string $validator): array|callable|string
	{
		return is_string($validator) && str_starts_with($validator, ':')
			? [Validator::class, 'validate' . ltrim($validator, ':')]
			: $validator;
	}
}
