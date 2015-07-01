<?php
/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;


/**
 * Defines method that must be implemented to allow a component to be validatable in form.
 *
 * @author     Ondrej Vlach
 */
interface IValidatable
{
	/**
	 * @return void
	 */
	function validate();
}
