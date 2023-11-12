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
 * Generates blueprint of form data class.
 * @deprecated use Nette\Latte\Blueprint::dataClass()
 */
final class DataClassGenerator
{
	public string $classNameSuffix = 'FormData';
	public bool $propertyPromotion = false;
	public bool $useSmartObject = true;


	/** @deprecated use Nette\Latte\Blueprint::dataClass() */
	public function generateCode(Form $form, ?string $baseName = null): string
	{
		return (new Blueprint)->generateDataClass($form, $this->propertyPromotion, $baseName);
	}
}
