<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;


/**
 * Common contract of validator objects. A validator inspects either the value alone
 * (ValueValidator) or the state of the control (ControlValidator).
 */
interface Validator
{
	/**
	 * Returns the client-side counterpart of the validator for the netteForms.js
	 * script, or null when the validator runs on the server only.
	 */
	function getClientRule(): ?ClientRule;
}
