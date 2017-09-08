<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms\Form;
use Nette\Utils\Strings;


/**
 * Implements the basic functionality common to text input controls.
 */
abstract class TextBase extends BaseControl
{
	/** @var string */
	protected $emptyValue = '';

	/** @var mixed unfiltered submitted value */
	protected $rawValue = '';

	/** @var bool */
	private $nullable;


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
		$this->value = $value;
		$this->rawValue = (string) $value;
		return $this;
	}


	/**
	 * Returns control's value.
	 * @return mixed
	 */
	public function getValue()
	{
		$value = $this->value === Strings::trim($this->translate($this->emptyValue)) ? '' : $this->value;
		return $this->nullable && $value === '' ? null : $value;
	}


	/**
	 * Sets whether getValue() returns null instead of empty string.
	 * @return static
	 */
	public function setNullable(bool $value = true)
	{
		$this->nullable = $value;
		return $this;
	}


	/**
	 * Sets the special value which is treated as empty string.
	 * @return static
	 */
	public function setEmptyValue(string $value)
	{
		$this->emptyValue = $value;
		return $this;
	}


	/**
	 * Returns the special value which is treated as empty string.
	 */
	public function getEmptyValue(): string
	{
		return $this->emptyValue;
	}


	/**
	 * Sets the maximum number of allowed characters.
	 * @return static
	 */
	public function setMaxLength(int $length)
	{
		$this->control->maxlength = $length;
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


	public function getControl(): Nette\Utils\Html
	{
		$el = parent::getControl();
		if ($this->emptyValue !== '') {
			$el->attrs['data-nette-empty-value'] = Strings::trim($this->translate($this->emptyValue));
		}
		if (isset($el->placeholder)) {
			$el->placeholder = $this->translate($el->placeholder);
		}
		return $el;
	}


	protected function getRenderedValue(): ?string
	{
		return $this->rawValue === ''
			? ($this->emptyValue === '' ? null : $this->translate($this->emptyValue))
			: $this->rawValue;
	}


	/**
	 * @return static
	 */
	public function addRule($validator, $errorMessage = null, $arg = null)
	{
		if ($validator === Form::LENGTH || $validator === Form::MAX_LENGTH) {
			$tmp = is_array($arg) ? $arg[1] : $arg;
			if (is_scalar($tmp)) {
				$this->control->maxlength = isset($this->control->maxlength) ? min($this->control->maxlength, $tmp) : $tmp;
			}
		}
		return parent::addRule($validator, $errorMessage, $arg);
	}
}
