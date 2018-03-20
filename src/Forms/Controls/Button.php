<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;


/**
 * Push button control with no default behavior.
 */
class Button extends BaseControl
{

	/**
	 * @param  string|object  $caption
	 */
	public function __construct($caption = null)
	{
		parent::__construct($caption);
		$this->control->type = 'button';
		$this->setOption('type', 'button');
	}


	/**
	 * Is button pressed?
	 */
	public function isFilled(): bool
	{
		$value = $this->getValue();
		return $value !== null && $value !== [];
	}


	/**
	 * Bypasses label generation.
	 */
	public function getLabel($caption = null): void
	{
	}


	/**
	 * Generates control's HTML element.
	 * @param  string|object  $caption
	 */
	public function getControl($caption = null): Nette\Utils\Html
	{
		$this->setOption('rendered', true);
		$el = clone $this->control;
		return $el->addAttributes([
			'name' => $this->getHtmlName(),
			'disabled' => $this->isDisabled(),
			'value' => $this->translate($caption === null ? $this->getCaption() : $caption),
		]);
	}
}
