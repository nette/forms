<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;


/**
 * Contract for form renderers.
 */
interface FormRenderer
{
	/**
	 * Renders the form into HTML string.
	 */
	function render(Form $form): string;
}
