<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms;


/**
 * Defines method that must implement form renderer.
 */
interface IFormRenderer
{

	/**
	 * Provides complete form rendering.
	 * @return string
	 */
	function render(Form $form);

}
