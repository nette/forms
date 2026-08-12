<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;


/**
 * Transforms the control value before subsequent rules are evaluated.
 * Its presence declares that the value changes on the server, which stops
 * client-side export of all following rules.
 */
interface Filter
{
	function filter(mixed $value): mixed;
}
