<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;


/**
 * Validates a value. Implemented by validators that inspect the value alone,
 * which makes them usable and testable outside of a form.
 */
interface ValueValidator extends Validator
{
	/**
	 * Returns null on success, or a message describing the failure.
	 */
	function validate(mixed $value): ?Message;
}
