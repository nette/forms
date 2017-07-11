<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Push button control with no default behavior.
 */
class Button extends BaseControl
{

	/**
	 * @param  string|object
	 */
	public function __construct($caption = null)
	{
		parent::__construct($caption);
		$this->control->type = 'button';
		$this->setOption('type', 'button');
	}


	/**
	 * Is button pressed?
	 * @return bool
	 */
	public function isFilled()
	{
		$value = $this->getValue();
		return $value !== null && $value !== [];
	}


	/**
	 * Bypasses label generation.
	 * @return void
	 */
	public function getLabel($caption = null)
	{
	}


	/**
	 * Generates control's HTML element.
	 * @param  string|object
	 * @return Nette\Utils\Html
	 */
	public function getControl($caption = null)
	{
		$this->setOption('rendered', true);
		$el = clone $this->control;
		return $el->addAttributes([
			'name' => $this->getHtmlName(),
			'disabled' => $this->isDisabled(),
			'value' => $this->translate($caption === null ? $this->caption : $caption),
		]);
	}
}
