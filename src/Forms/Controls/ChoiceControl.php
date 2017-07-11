<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Choice control that allows single item selection.
 *
 * @property   array $items
 * @property-read mixed $selectedItem
 */
abstract class ChoiceControl extends BaseControl
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
		$this->value = $this->getHttpData(Nette\Forms\Form::DATA_TEXT);
		if ($this->value !== null) {
			if (is_array($this->disabled) && isset($this->disabled[$this->value])) {
				$this->value = null;
			} else {
				$this->value = key([$this->value => null]);
			}
		}
	}


	/**
	 * Sets selected item (by key).
	 * @param  string|int
	 * @return static
	 * @internal
	 */
	public function setValue($value)
	{
		if ($this->checkAllowedValues && $value !== null && !array_key_exists((string) $value, $this->items)) {
			$set = Nette\Utils\Strings::truncate(implode(', ', array_map(function ($s) { return var_export($s, true); }, array_keys($this->items))), 70, '...');
			throw new Nette\InvalidArgumentException("Value '$value' is out of allowed set [$set] in field '{$this->name}'.");
		}
		$this->value = $value === null ? null : key([(string) $value => null]);
		return $this;
	}


	/**
	 * Returns selected key.
	 * @return string|int
	 */
	public function getValue()
	{
		return array_key_exists($this->value, $this->items) ? $this->value : null;
	}


	/**
	 * Returns selected key (not checked).
	 * @return string|int
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
		return $this->getValue() !== null;
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
	 * Returns selected value.
	 * @return mixed
	 */
	public function getSelectedItem()
	{
		$value = $this->getValue();
		return $value === null ? null : $this->items[$value];
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
		if (isset($this->disabled[$this->value])) {
			$this->value = null;
		}
		return $this;
	}
}
