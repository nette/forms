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
 * Choice control that allows multiple items selection.
 *
 * @property   array $items
 * @property-read array $selectedItems
 */
abstract class MultiChoiceControl extends BaseControl
{
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
		$this->value = array_keys(array_flip($this->getHttpData(Nette\Forms\Form::DataText)));
		if (is_array($this->disabled)) {
			$this->value = array_diff($this->value, array_keys($this->disabled));
		}
	}


	/**
	 * Sets selected items (by keys).
	 * @return static
	 * @internal
	 */
	public function setValue($values)
	{
		if (is_scalar($values) || $values === null) {
			$values = (array) $values;
		} elseif (!is_array($values)) {
			throw new Nette\InvalidArgumentException(sprintf("Value must be array or null, %s given in field '%s'.", get_debug_type($values), $this->getName()));
		}

		$flip = [];
		foreach ($values as $value) {
			if ($value instanceof \BackedEnum) {
				$value = $value->value;
			} elseif (!is_scalar($value) && !$value instanceof \Stringable) {
				throw new Nette\InvalidArgumentException(sprintf("Values must be scalar, %s given in field '%s'.", get_debug_type($value), $this->getName()));
			}

			$flip[(string) $value] = true;
		}

		$values = array_keys($flip);
		if ($this->checkDefaultValue && ($diff = array_diff($values, $tmp = array_column($this->choices, 0)))) {
			$set = Nette\Utils\Strings::truncate(implode(', ', array_map(fn($s) => var_export($s, return: true), $tmp)), 70, '...');
			$vals = (count($diff) > 1 ? 's' : '') . " '" . implode("', '", $diff) . "'";
			throw new Nette\InvalidArgumentException("Value$vals are out of allowed set [$set] in field '{$this->getName()}'.");
		}

		$this->value = $values;
		return $this;
	}


	/**
	 * Returns selected keys.
	 */
	public function getValue(): array
	{
		return array_values(array_intersect($this->value, array_column($this->choices, 0)));
	}


	/**
	 * Returns selected keys (not checked).
	 */
	public function getRawValue(): array
	{
		return $this->value;
	}


	/**
	 * Sets items from which to choose.
	 * @return static
	 */
	public function setItems(array $items, bool $useKeys = true)
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
	 * Returns selected values.
	 */
	public function getSelectedItems(): array
	{
		$flip = array_flip($this->value);
		$res = array_filter($this->choices, fn($choice) => isset($flip[$choice[0]]));
		return array_column($res, 1, 0);
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
		$this->disabled = array_fill_keys($value, value: true);
		$this->value = array_diff($this->value, $value);
		return $this;
	}


	/**
	 * Returns HTML name of control.
	 */
	public function getHtmlName(): string
	{
		return parent::getHtmlName() . '[]';
	}


	public function checkDefaultValue(bool $value = true): static
	{
		$this->checkDefaultValue = $value;
		return $this;
	}
}
