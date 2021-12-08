<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Stringable;


/**
 * Select box control that allows single item selection.
 */
class SelectBox extends ChoiceControl
{
	/** validation rule */
	public const VALID = ':selectBoxValid';

	/** of option / optgroup */
	private array $options = [];

	private string|Stringable|false $prompt = false;

	private array $optionAttributes = [];


	public function __construct($label = null, array $items = null)
	{
		parent::__construct($label, $items);
		$this->setOption('type', 'select');
		$this->addCondition(
			fn() => $this->prompt === false
			&& $this->options
			&& $this->control->size < 2,
		)->addRule(Nette\Forms\Form::FILLED, Nette\Forms\Validator::$messages[self::VALID]);
	}


	/**
	 * Sets first prompt item in select box.
	 */
	public function setPrompt(string|Stringable|false $prompt): static
	{
		$this->prompt = $prompt;
		return $this;
	}


	/**
	 * Returns first prompt item?
	 */
	public function getPrompt(): string|Stringable|false
	{
		return $this->prompt;
	}


	/**
	 * Sets options and option groups from which to choose.
	 */
	public function setItems(array $items, bool $useKeys = true): static
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


	public function getControl(): Nette\Utils\Html
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
			$this->value,
		)->addAttributes(parent::getControl()->attrs);
	}


	public function addOptionAttributes(array $attributes): static
	{
		$this->optionAttributes = $attributes + $this->optionAttributes;
		return $this;
	}


	public function setOptionAttribute(string $name, mixed $value = true): static
	{
		$this->optionAttributes[$name] = $value;
		return $this;
	}


	public function isOk(): bool
	{
		return $this->isDisabled()
			|| $this->prompt !== false
			|| $this->getValue() !== null
			|| !$this->options
			|| $this->control->size > 1;
	}


	public function getOptionAttributes(): array
	{
		return $this->optionAttributes;
	}
}
