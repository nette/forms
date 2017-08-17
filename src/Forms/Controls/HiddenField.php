<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Hidden form control used to store a non-displayed value.
 */
class HiddenField extends BaseControl
{
	/** @var bool */
	private $persistValue;


	public function __construct($persistentValue = null)
	{
		parent::__construct();
		$this->control->type = 'hidden';
		$this->setOption('type', 'hidden');
		if ($persistentValue !== null) {
			$this->unmonitor(Nette\Forms\Form::class);
			$this->persistValue = true;
			$this->value = (string) $persistentValue;
		}
	}


	/**
	 * Sets control's value.
	 * @param  string
	 * @return static
	 * @internal
	 */
	public function setValue($value)
	{
		if (!is_scalar($value) && $value !== null && !method_exists($value, '__toString')) {
			throw new Nette\InvalidArgumentException(sprintf("Value must be scalar or null, %s given in field '%s'.", gettype($value), $this->name));
		}
		if (!$this->persistValue) {
			$this->value = (string) $value;
		}
		return $this;
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$this->setOption('rendered', true);
		$el = clone $this->control;
		return $el->addAttributes([
			'name' => $this->getHtmlName(),
			'disabled' => $this->isDisabled(),
			'value' => $this->value,
		]);
	}


	/**
	 * Bypasses label generation.
	 * @param  string|object
	 * @return void
	 */
	public function getLabel($caption = null)
	{
	}


	/**
	 * Adds error message to the list.
	 * @param  string|object
	 * @return void
	 */
	public function addError($message, $translate = true)
	{
		$this->getForm()->addError($message, $translate);
	}
}
