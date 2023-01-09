<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Rendering;

use Nette\Forms\Blueprint;
use Nette\Forms\Form;


/**
 * Generates Latte blueprint of form.
 * @deprecated use Nette\Latte\Blueprint::latte()
 */
final class LatteRenderer
{
	/** @deprecated use Nette\Latte\Blueprint::latte() */
	public function render(Form $form): string
	{
		trigger_error(__METHOD__ . '() is deprecated, use ' . Blueprint::class . '::latte()', E_USER_DEPRECATED);
		return (new Blueprint)->generateLatte($form);
	}
}
