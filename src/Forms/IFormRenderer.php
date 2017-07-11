<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms;


/**
 * Defines method that must implement form renderer.
 */
interface IFormRenderer
{

	/**
	 * Provides complete form rendering.
	 */
	function render(Form $form): string;
}
