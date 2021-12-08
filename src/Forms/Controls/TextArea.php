<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Stringable;


/**
 * Multiline text input control.
 */
class TextArea extends TextBase
{
	public function __construct(string|Stringable $label = null)
	{
		parent::__construct($label);
		$this->control->setName('textarea');
		$this->setOption('type', 'textarea');
	}


	public function getControl(): Nette\Utils\Html
	{
		return parent::getControl()
			->setText((string) $this->getRenderedValue());
	}
}
