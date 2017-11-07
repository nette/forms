<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;


/**
 * Hidden form control used to store a non-displayed value.
 */
class HiddenField extends BaseControl
{
	/** @var mixed unfiltered submitted value */
	protected $rawValue = '';

	/** @var bool */
	private $persistValue;

	/** @var bool */
	private $nullable;


	public function __construct($persistentValue = null)
	{
		parent::__construct();
		$this->control->type = 'hidden';
		$this->setOption('type', 'hidden');
		if ($persistentValue !== null) {
			$this->unmonitor(Nette\Forms\Form::class);
			$this->persistValue = true;
			$this->value = $persistentValue;
			$this->rawValue = (string) $persistentValue;
		}
	}


	/**
	 * Sets control's value.
	 * @return static
	 * @internal
	 */
	public function setValue($value)
	{
		if ($value === null) {
			$value = '';
		} elseif (!is_scalar($value) && !method_exists($value, '__toString')) {
			throw new Nette\InvalidArgumentException(sprintf("Value must be scalar or null, %s given in field '%s'.", gettype($value), $this->name));
		}
		if (!$this->persistValue) {
			$this->rawValue = (string) $value;
			$this->value = $value;
		}
		return $this;
	}


	/**
	 * Returns control's value.
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->nullable && $this->value === '' ? null : $this->value;
	}


	/**
	 * Sets whether getValue() returns NULL instead of empty string.
	 * @return static
	 */
	public function setNullable(bool $value = true)
	{
		$this->nullable = $value;
		return $this;
	}


	/**
	 * Appends input string filter callback.
	 * @return static
	 */
	public function addFilter(callable $filter)
	{
		$this->getRules()->addFilter($filter);
		return $this;
	}


	/**
	 * Generates control's HTML element.
	 */
	public function getControl(): Nette\Utils\Html
	{
		$this->setOption('rendered', true);
		$el = clone $this->control;
		return $el->addAttributes([
			'name' => $this->getHtmlName(),
			'disabled' => $this->isDisabled(),
			'value' => $this->rawValue,
		]);
	}


	/**
	 * Bypasses label generation.
	 * @param  string|object
	 */
	public function getLabel($caption = null): void
	{
	}


	/**
	 * Adds error message to the list.
	 * @param  string|object
	 */
	public function addError($message, bool $translate = true): void
	{
		$this->getForm()->addError($message, $translate);
	}
}
