<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Choice control that allows multiple items selection.
 *
 * @property   array $items
 * @property-read array $selectedItems
 */
abstract class MultiChoiceControl extends BaseControl
{
	/** @var bool */
	public $checkAllowedValues = true;

	/** @var array */
	private $items = [];


	public function __construct($label = null, array $items = null)
	{
		parent::__construct($label);
		if ($items !== null) {
			$this->setItems($items);
		}
	}


	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$this->value = array_keys(array_flip($this->getHttpData(Nette\Forms\Form::DATA_TEXT)));
		if (is_array($this->disabled)) {
			$this->value = array_diff($this->value, array_keys($this->disabled));
		}
	}


	/**
	 * Sets selected items (by keys).
	 * @param  array
	 * @return static
	 * @internal
	 */
	public function setValue($values)
	{
		if (is_scalar($values) || $values === null) {
			$values = (array) $values;
		} elseif (!is_array($values)) {
			throw new Nette\InvalidArgumentException(sprintf("Value must be array or null, %s given in field '%s'.", gettype($values), $this->name));
		}
		$flip = [];
		foreach ($values as $value) {
			if (!is_scalar($value) && !method_exists($value, '__toString')) {
				throw new Nette\InvalidArgumentException(sprintf("Values must be scalar, %s given in field '%s'.", gettype($value), $this->name));
			}
			$flip[(string) $value] = true;
		}
		$values = array_keys($flip);
		if ($this->checkAllowedValues && ($diff = array_diff($values, array_keys($this->items)))) {
			$set = Nette\Utils\Strings::truncate(implode(', ', array_map(function ($s) { return var_export($s, true); }, array_keys($this->items))), 70, '...');
			$vals = (count($diff) > 1 ? 's' : '') . " '" . implode("', '", $diff) . "'";
			throw new Nette\InvalidArgumentException("Value$vals are out of allowed set [$set] in field '{$this->name}'.");
		}
		$this->value = $values;
		return $this;
	}


	/**
	 * Returns selected keys.
	 * @return array
	 */
	public function getValue()
	{
		return array_values(array_intersect($this->value, array_keys($this->items)));
	}


	/**
	 * Returns selected keys (not checked).
	 * @return array
	 */
	public function getRawValue()
	{
		return $this->value;
	}


	/**
	 * Is any item selected?
	 * @return bool
	 */
	public function isFilled()
	{
		return $this->getValue() !== [];
	}


	/**
	 * Sets items from which to choose.
	 * @param  array
	 * @param  bool
	 * @return static
	 */
	public function setItems(array $items, $useKeys = true)
	{
		$this->items = $useKeys ? $items : array_combine($items, $items);
		return $this;
	}


	/**
	 * Returns items from which to choose.
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}


	/**
	 * Returns selected values.
	 * @return array
	 */
	public function getSelectedItems()
	{
		return array_intersect_key($this->items, array_flip($this->value));
	}


	/**
	 * Disables or enables control or items.
	 * @param  bool|array
	 * @return static
	 */
	public function setDisabled($value = true)
	{
		if (!is_array($value)) {
			return parent::setDisabled($value);
		}

		parent::setDisabled(false);
		$this->disabled = array_fill_keys($value, true);
		$this->value = array_diff($this->value, $value);
		return $this;
	}


	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName()
	{
		return parent::getHtmlName() . '[]';
	}
}
