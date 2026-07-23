<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;


/**
 * Source of submitted data for a form. Experimental.
 */
interface SubmissionSource
{
	/**
	 * Returns raw submitted data or null when the form was not submitted via this source.
	 * @return ?mixed[]
	 */
	function receiveData(Form $form): ?array;

	/**
	 * Called once before the form is rendered; lets the source install its marker fields
	 * (the standalone tracker, the presenter signal field) into the form.
	 */
	function prepare(Form $form): void;
}
