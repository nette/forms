<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;
use function is_array;


/**
 * Select box control that allows multiple items selection.
 */
class MultiSelectBox extends MultiChoiceControl
{
	/** @var mixed[]  option / optgroup */
	private array $options = [];

	/** @var array<string, mixed> */
	private array $optionAttributes = [];


	/** @param  ?mixed[]  $items */
	public function __construct(string|\Stringable|null $label = null, ?array $items = null)
	{
		parent::__construct($label, $items);
		$this->setOption('type', 'select');
	}


	/**
	 * Sets options and option groups from which to choose.
	 * @param  mixed[]  $items
	 * @return static
	 */
	public function setItems(array $items, bool $useKeys = true)
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
		return parent::setItems(Nette\Utils\Arrays::flatten($items, preserveKeys: true));
	}


	public function getControl(): Nette\Utils\Html
	{
		$items = [];
		foreach ($this->options as $key => $value) {
			$items[is_array($value) ? $this->translate($key) : $key] = $this->translate($value);
		}

		return Nette\Forms\Helpers::createSelectBox(
			$items,
			[
				'disabled:' => is_array($this->disabled) ? $this->disabled : null,
			] + $this->optionAttributes,
			$this->value,
		)->addAttributes(parent::getControl()->attrs)->multiple(true);
	}


	/**
	 * @param  array<string, mixed>  $attributes
	 * @deprecated use setOptionAttribute()
	 */
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


	/** @return array<string, mixed> */
	public function getOptionAttributes(): array
	{
		return $this->optionAttributes;
	}
}
