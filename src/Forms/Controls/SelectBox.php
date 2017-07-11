<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Select box control that allows single item selection.
 */
class SelectBox extends ChoiceControl
{
	/** validation rule */
	const VALID = ':selectBoxValid';

	/** @var array of option / optgroup */
	private $options = [];

	/** @var mixed */
	private $prompt = false;

	/** @var array */
	private $optionAttributes = [];


	public function __construct($label = null, array $items = null)
	{
		parent::__construct($label, $items);
		$this->setOption('type', 'select');
		$this->addCondition(Nette\Forms\Form::BLANK)
			->addRule([$this, 'isOk'], Nette\Forms\Validator::$messages[self::VALID]);
	}


	/**
	 * Sets first prompt item in select box.
	 * @param  string|object
	 * @return static
	 */
	public function setPrompt($prompt)
	{
		$this->prompt = $prompt;
		return $this;
	}


	/**
	 * Returns first prompt item?
	 * @return mixed
	 */
	public function getPrompt()
	{
		return $this->prompt;
	}


	/**
	 * Sets options and option groups from which to choose.
	 * @return static
	 */
	public function setItems(array $items, $useKeys = true)
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
		return parent::setItems(Nette\Utils\Arrays::flatten($items, true));
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$items = $this->prompt === false ? [] : ['' => $this->translate($this->prompt)];
		foreach ($this->options as $key => $value) {
			$items[is_array($value) ? $this->translate($key) : $key] = $this->translate($value);
		}

		return Nette\Forms\Helpers::createSelectBox(
			$items,
			[
				'disabled:' => is_array($this->disabled) ? $this->disabled : null,
			] + $this->optionAttributes,
			$this->value
		)->addAttributes(parent::getControl()->attrs);
	}


	/**
	 * @return static
	 */
	public function addOptionAttributes(array $attributes)
	{
		$this->optionAttributes = $attributes + $this->optionAttributes;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isOk()
	{
		return $this->isDisabled()
			|| $this->prompt !== false
			|| $this->getValue() !== null
			|| !$this->options
			|| $this->control->size > 1;
	}
}
