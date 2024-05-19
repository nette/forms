<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Utils\Arrays;


/**
 * Choice control that allows single item selection.
 *
 * @property   array $items
 * @property-read mixed $selectedItem
 */
abstract class ChoiceControl extends BaseControl
{
	/** @var bool[] */
	protected array $disabledChoices = [];
	private bool $checkDefaultValue = true;

	/** @var list<array{int|string, string|\Stringable}> */
	private array $choices = [];


	public function __construct($label = null, ?array $items = null)
	{
		parent::__construct($label);
		if ($items !== null) {
			$this->setItems($items);
		}
	}


	public function loadHttpData(): void
	{
		$value = $this->getHttpData(Nette\Forms\Form::DataText);
		$this->value = $value === null ? null : Arrays::toKey($value);
	}


	/**
	 * Sets selected item (by key).
	 * @param  string|int|\BackedEnum|\Stringable|null  $value
	 * @internal
	 */
	public function setValue($value): static
	{
		if ($value === null) {
			$this->value = null;
			return $this;
		} elseif ($value instanceof \BackedEnum) {
			$value = $value->value;
		} elseif (!is_string($value) && !is_int($value) && !$value instanceof \Stringable) { // do ChoiceControl
			throw new Nette\InvalidArgumentException(sprintf('Value must be scalar|enum|Stringable, %s given.', get_debug_type($value)));
		}

		$value = Arrays::toKey((string) $value);
		if ($this->checkDefaultValue && !Arrays::some($this->choices, fn($choice) => $choice[0] === $value)) {
			$set = Nette\Utils\Strings::truncate(implode(', ', array_map(fn($choice) => var_export($choice[0], return: true), $this->choices)), 70, '...');
			throw new Nette\InvalidArgumentException("Value '$value' is out of allowed set [$set] in field '{$this->getName()}'.");
		}

		$this->value = $value;
		return $this;
	}


	/**
	 * Returns selected key.
	 * @return string|int|null
	 */
	public function getValue(): mixed
	{
		return $this->value !== null
			&& !isset($this->disabledChoices[$this->value])
			&& ([$res] = Arrays::first($this->choices, fn($choice) => $choice[0] === $this->value))
			? $res
			: null;
	}


	/**
	 * Returns selected key (not checked).
	 */
	public function getRawValue(): string|int|null
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
		$this->choices = [];
		foreach ($items as $k => $v) {
			$this->choices[] = [$useKeys ? $k : Arrays::toKey((string) $v), $v];
		}
		return $this;
	}


	/**
	 * Returns items from which to choose.
	 */
	public function getItems(): array
	{
		return array_column($this->choices, 1, 0);
	}


	/**
	 * Returns selected value.
	 */
	public function getSelectedItem(): mixed
	{
		return $this->value !== null
			&& !isset($this->disabledChoices[$this->value])
			&& ([, $res] = Arrays::first($this->choices, fn($choice) => $choice[0] === $this->value))
			? $res
			: null;
	}


	/**
	 * Disables or enables control or items.
	 */
	public function setDisabled(bool|array $value = true): static
	{
		if (!is_array($value)) {
			$this->disabledChoices = [];
			return parent::setDisabled($value);
		}
		$this->disabledChoices = array_fill_keys($value, value: true);
		return parent::setDisabled(false);
	}


	public function checkDefaultValue(bool $value = true): static
	{
		$this->checkDefaultValue = $value;
		return $this;
	}
}
