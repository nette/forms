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
 * Single validation rule or condition represented as value object.
 */
final readonly class Rule
{
	public function __construct(
		public Control $control,
		/** @var Validation\Validator|Validation\Filter|(callable(never, never): bool)|string */
		public mixed $validator,
		public mixed $arg = null,
		public string|Stringable|null $message = null,
		public bool $isNegative = false,
		public ?Rules $branch = null,
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
	 * Client-side counterparts of the rule's validator, empty for server-only rules.
	 * @internal
	 * @return list<Validation\ClientRule>
	 */
	public function getClientRules(): array
	{
		return $this->validator instanceof Validation\Validator
			? $this->validator->getClientRules()
			: [];
	}


	/**
	 * Does the rule check that the control is filled in, i.e. makes it mandatory?
	 * @internal
	 */
	public function checksFilled(): bool
	{
		return !$this->isNegative
			&& ($this->getOp() === Form::Filled || $this->validator instanceof Validation\FilledValidator);
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
			? ($this->getOp() === Form::Filled || $this->validator instanceof Validation\FilledValidator)
			: ($this->getOp() === Form::Blank || $this->validator instanceof Validation\BlankValidator);
	}


	/** @internal */
	public function canExport(): bool
	{
		return !$this->mutatesValue()
			&& (is_string($this->validator)
				|| $this->getClientRules() !== []
				|| (is_callable($this->validator) && Nette\Utils\Callback::isStatic($this->validator)));
	}
}
