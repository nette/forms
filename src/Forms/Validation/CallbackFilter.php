<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Forms\Control;


/**
 * Adapts a plain callable given to Rules::addFilter() to the Filter interface.
 * Stays invokable with the legacy rule-validator signature so that code
 * treating rule validators as callables keeps working.
 * @internal
 */
final readonly class CallbackFilter implements Filter
{
	public function __construct(
		/** @var \Closure(mixed): mixed */
		private \Closure $callback,
	) {
	}


	public function filter(mixed $value): mixed
	{
		return ($this->callback)($value);
	}


	public function __invoke(Control $control): bool
	{
		$control->setValue($this->filter($control->getValue()));
		return true;
	}
}
