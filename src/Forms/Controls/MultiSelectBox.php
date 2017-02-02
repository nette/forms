<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Select box control that allows multiple items selection.
 */
class MultiSelectBox extends MultiChoiceControl
{
	/** @var array of option / optgroup */
	private $options = [];

	/** @var bool */
	private $translateOptions = TRUE;

	/** @var array */
	private $optionAttributes = [];


	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);
		$this->setOption('type', 'select');
	}


	/**
	 * Disable translation of select options.
	 * @param  bool
	 * @return static
	 */
	public function setTranslateOptions($translateOptions = TRUE)
	{
		$this->translateOptions = (bool) $translateOptions;
		return $this;
	}


	/**
	 * Sets options and option groups from which to choose.
	 * @return static
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		if (!$useKeys) {
			$res = [];
			foreach ($items as $key => $value) {
				unset($items[$key]);
				if (is_array($value)) {
					foreach ($value as $val) {
						$res[$key][(string) $val] = $val;
					}
				} else {
					$res[(string) $value] = $value;
				}
			}
			$items = $res;
		}
		$this->options = $items;
		return parent::setItems(Nette\Utils\Arrays::flatten($items, TRUE));
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$items = [];
		foreach ($this->options as $key => $value) {
			if (is_array($value) && $this->translateOptions) {
				$key = $this->translate($key);
			}

			$items[$key] = $this->translateOptions ? $this->translate($value) : $value;
		}

		return Nette\Forms\Helpers::createSelectBox(
			$items,
			[
				'selected?' => $this->value,
				'disabled:' => is_array($this->disabled) ? $this->disabled : NULL,
			] + $this->optionAttributes
		)->addAttributes(parent::getControl()->attrs)->multiple(TRUE);
	}


	/**
	 * @return static
	 */
	public function addOptionAttributes(array $attributes)
	{
		$this->optionAttributes = $attributes + $this->optionAttributes;
		return $this;
	}

}
