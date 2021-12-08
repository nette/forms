<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

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
	private bool $checkDefaultValue = true;

	private array $items = [];


	public function __construct($label = null, array $items = null)
	{
		parent::__construct($label);
		if ($items !== null) {
			$this->setItems($items);
		}
	}


	public function loadHttpData(): void
	{
		$this->value = $this->getHttpData(Nette\Forms\Form::DATA_TEXT);
		if ($this->value !== null) {
			$this->value = is_array($this->disabled) && isset($this->disabled[$this->value])
				? null
				: key([$this->value => null]);
		}
	}


	/**
	 * Sets selected item (by key).
	 * @param  string|int|null  $value
	 * @internal
	 */
	public function setValue($value): static
	{
		if ($this->checkDefaultValue && $value !== null && !array_key_exists((string) $value, $this->items)) {
			$set = Nette\Utils\Strings::truncate(
				implode(', ', array_map(fn($s) => var_export($s, true), array_keys($this->items))),
				70,
				'...',
			);
			throw new Nette\InvalidArgumentException("Value '$value' is out of allowed set [$set] in field '{$this->name}'.");
		}
		$this->value = $value === null ? null : key([(string) $value => null]);
		return $this;
	}


	/**
	 * Returns selected key.
	 * @return string|int|null
	 */
	public function getValue(): mixed
	{
		return array_key_exists($this->value, $this->items)
			? $this->value
			: null;
	}


	/**
	 * Returns selected key (not checked).
	 */
	public function getRawValue(): string|int
	{
		return $this->value;
	}


	/**
	 * Is any item selected?
	 */
	public function isFilled(): bool
	{
		return $this->getValue() !== null;
	}


	/**
	 * Sets items from which to choose.
	 */
	public function setItems(array $items, bool $useKeys = true): static
	{
		$this->items = $useKeys ? $items : array_combine($items, $items);
		return $this;
	}


	/**
	 * Returns items from which to choose.
	 */
	public function getItems(): array
	{
		return $this->items;
	}


	/**
	 * Returns selected value.
	 */
	public function getSelectedItem(): mixed
	{
		$value = $this->getValue();
		return $value === null ? null : $this->items[$value];
	}


	/**
	 * Disables or enables control or items.
	 */
	public function setDisabled(bool|array $value = true): static
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


	public function checkDefaultValue(bool $value = true): static
	{
		$this->checkDefaultValue = $value;
		return $this;
	}
}
