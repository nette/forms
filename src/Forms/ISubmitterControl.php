<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms;


/**
 * Defines method that must be implemented to allow a control to submit web form.
 */
interface ISubmitterControl extends IControl
{
	/**
	 * Gets the validation scope. Clicking the button validates only the controls within the specified scope.
	 */
	function getValidationScope(): ?array;
}
