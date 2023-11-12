<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms;

use Nette;


/**
 * Single validation rule or condition represented as value object.
 */
class Rule
{
	/** @var Control */
	public $control;

	/** @var mixed */
	public $validator;

	/** @var mixed */
	public $arg;

	/** @var bool */
	public $isNegative = false;

	/** @var string|null */
	public $message;

	/** @var Rules|null  for conditions */
	public $branch;


	/** @internal */
	public function canExport(): bool
	{
		return is_string($this->validator)
			|| Nette\Utils\Callback::isStatic($this->validator);
	}
}
