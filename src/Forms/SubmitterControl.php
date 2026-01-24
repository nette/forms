<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;


/**
 * Contract for controls that can submit a form.
 */
interface SubmitterControl extends Control
{
	/**
	 * Returns the validation scope. Clicking the button validates only the controls within the scope, or null for all.
	 */
	function getValidationScope(): ?array;
}


interface_exists(ISubmitterControl::class);
