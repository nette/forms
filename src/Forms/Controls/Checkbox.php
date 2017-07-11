<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;
use Nette\Utils\Html;


/**
 * Check box control. Allows the user to select a true or false condition.
 */
class Checkbox extends BaseControl
{
	/** @var Html  wrapper element template */
	private $wrapper;


	/**
	 * @param  string|object
	 */
	public function __construct($label = null)
	{
		parent::__construct($label);
		$this->control->type = 'checkbox';
		$this->wrapper = Html::el();
		$this->setOption('type', 'checkbox');
	}


	/**
	 * Sets control's value.
	 * @param  bool
	 * @return static
	 * @internal
	 */
	public function setValue($value)
	{
		if (!is_scalar($value) && $value !== null) {
			throw new Nette\InvalidArgumentException(sprintf("Value must be scalar or null, %s given in field '%s'.", gettype($value), $this->name));
		}
		$this->value = (bool) $value;
		return $this;
	}


	/**
	 * Is control filled?
	 * @return bool
	 */
	public function isFilled()
	{
		return $this->getValue() !== false; // back compatibility
	}


	/**
	 * Generates control's HTML element.
	 * @return Html
	 */
	public function getControl()
	{
		return $this->wrapper->setHtml($this->getLabelPart()->insert(0, $this->getControlPart()));
	}


	/**
	 * Bypasses label generation.
	 * @return void
	 */
	public function getLabel($caption = null)
	{
	}


	/**
	 * @return Html
	 */
	public function getControlPart()
	{
		return parent::getControl()->checked($this->value);
	}


	/**
	 * @return Html
	 */
	public function getLabelPart()
	{
		return parent::getLabel();
	}


	/**
	 * Returns wrapper HTML element template.
	 * @return Html
	 */
	public function getSeparatorPrototype()
	{
		return $this->wrapper;
	}
}
