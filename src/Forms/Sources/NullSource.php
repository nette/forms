<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Sources;

use Nette\Forms\{Form, SubmissionSource};


/**
 * Source for forms that are never submitted: render-only forms, item templates, blueprints. Experimental.
 */
final class NullSource implements SubmissionSource
{
	public function receiveData(Form $form): ?array
	{
		return null;
	}


	public function prepare(Form $form): void
	{
	}
}
