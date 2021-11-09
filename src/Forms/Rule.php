<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms;

use Nette;
use Stringable;


/**
 * Single validation rule or condition represented as value object.
 */
final class Rule
{
	public Control $control;
	public mixed $validator;
	public mixed $arg = null;
	public bool $isNegative = false;
	public string|Stringable|null $message;
	public ?Rules $branch = null;


	/** @internal */
	public function canExport(): bool
	{
		return is_string($this->validator)
			|| Nette\Utils\Callback::isStatic($this->validator);
	}
}
