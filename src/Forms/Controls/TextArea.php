<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Multiline text input control.
 */
class TextArea extends TextBase
{

	/**
	 * @param  string|object
	 */
	public function __construct($label = null)
	{
		parent::__construct($label);
		$this->control->setName('textarea');
		$this->setOption('type', 'textarea');
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		return parent::getControl()
			->setText($this->getRenderedValue());
	}
}
