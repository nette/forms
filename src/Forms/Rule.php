<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use Stringable;
use function is_string;


/**
 * Single validation rule or condition represented as an immutable value object.
 */
final class Rule
{
	private ?Validation\Validator $resolved = null;
	private bool $isResolved = false;


	public function __construct(
		public readonly Control $control,
		/** @var Validation\Validator|Validation\Filter|(callable(never, never): bool)|string */
		public readonly mixed $validator,
		public readonly mixed $arg = null,
		public readonly string|Stringable|null $message = null,
		public readonly bool $isNegative = false,
		public readonly ?Rules $branch = null,
	) {
	}


	/**
	 * Canonical operation identifier (e.g. ':email'), or null for callables and validator objects.
	 * @internal
	 */
	public function getOp(): ?string
	{
		return is_string($this->validator) ? $this->validator : null;
	}


	/**
	 * Does the rule change the control value on the server in a way the client cannot
	 * reproduce? A filter that also provides client rules (e.g. integer normalization)
	 * is reproduced by its JS counterpart and does not count.
	 * @internal
	 */
	public function mutatesValue(): bool
	{
		return $this->validator instanceof Validation\Filter
			&& $this->getClientRules() === [];
	}


	/**
	 * Returns the validator object performing this rule: either the object given
	 * to addRule() directly, or the shared object the string operation maps to.
	 * Null for callables and operations without an object counterpart.
	 * The rule is immutable, so the resolution is memoized.
	 * @internal
	 */
	public function getValidationObject(): ?Validation\Validator
	{
		if (!$this->isResolved) {
			$this->resolved = match (true) {
				$this->validator instanceof Validation\Validator => $this->validator,
				is_string($this->validator) => Validation\Operations::toValidator($this->validator, $this->arg, $this->control),
				default => null,
			};
			$this->isResolved = true;
		}

		return $this->resolved;
	}


	/**
	 * Client-side counterparts of the rule's validator, empty for server-only rules.
	 * @internal
	 * @return list<Validation\ClientRule>
	 */
	public function getClientRules(): array
	{
		return $this->getValidationObject()?->getClientRules() ?? [];
	}


	/**
	 * Does the rule check that the control is filled in, i.e. makes it mandatory?
	 * @internal
	 */
	public function checksFilled(): bool
	{
		return !$this->isNegative
			&& $this->getValidationObject() instanceof Validation\FilledValidator;
	}


	/**
	 * Does the rule check that the control is blank, so it is evaluated even for
	 * an empty optional control? A negated filled check (the else branch of
	 * a condition with a validator object) counts as well.
	 * @internal
	 */
	public function checksBlank(): bool
	{
		return $this->isNegative
			? $this->getValidationObject() instanceof Validation\FilledValidator
			: $this->getValidationObject() instanceof Validation\BlankValidator;
	}


	/**
	 * Name of the validation callback in Class::method form when the rule uses
	 * a statically callable validator (including first-class callable syntax), or null.
	 * The JS counterpart is registered under this name in Nette.validators.
	 * @internal
	 */
	public function getCallableName(): ?string
	{
		$validator = $this->validator;
		if ($validator instanceof \Closure) {
			$validator = Nette\Utils\Callback::unwrap($validator);
		}

		return !$validator instanceof Validation\Validator
			&& is_callable($validator)
			&& Nette\Utils\Callback::isStatic($validator)
			? Nette\Utils\Callback::toString($validator)
			: null;
	}


	/** @internal */
	public function canExport(): bool
	{
		return !$this->mutatesValue()
			&& (is_string($this->validator)
				|| $this->getClientRules() !== []
				|| $this->getCallableName() !== null);
	}
}
