<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Forms\Control;


/**
 * Validates the state of a control rather than its value alone, e.g. whether it is filled in.
 */
interface ControlValidator extends Validator
{
	/**
	 * Returns null on success, or a message describing the failure.
	 */
	function validateControl(Control $control): ?Message;
}
