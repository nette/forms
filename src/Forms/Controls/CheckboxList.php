<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;
use Nette\Utils\Html;


/**
 * Set of checkboxes.
 *
 * @property-read Html $separatorPrototype
 * @property-read Html $containerPrototype
 * @property-read Html $itemLabelPrototype
 */
class CheckboxList extends MultiChoiceControl
{
	/** @var Html  separator element template */
	protected $separator;

	/** @var Html  container element template */
	protected $container;

	/** @var Html  item label template */
	protected $itemLabel;


	/**
	 * @param  string|object
	 */
	public function __construct($label = null, array $items = null)
	{
		parent::__construct($label, $items);
		$this->control->type = 'checkbox';
		$this->container = Html::el();
		$this->separator = Html::el('br');
		// $this->itemLabel = Html::el('label'); back compatiblity
		$this->setOption('type', 'checkbox');
	}


	/**
	 * Generates control's HTML element.
	 * @return Html
	 */
	public function getControl()
	{
		$input = parent::getControl();
		$items = $this->getItems();
		reset($items);

		return $this->container->setHtml(
			Nette\Forms\Helpers::createInputList(
				$this->translate($items),
				array_merge($input->attrs, [
					'id' => null,
					'checked?' => $this->value,
					'disabled:' => $this->disabled,
					'required' => null,
					'data-nette-rules:' => [key($items) => $input->attrs['data-nette-rules']],
				]),
				$this->itemLabel ? $this->itemLabel->attrs : $this->label->attrs,
				$this->separator
			)
		);
	}


	/**
	 * Generates label's HTML element.
	 * @param  string|object
	 * @return Html
	 */
	public function getLabel($caption = null)
	{
		return parent::getLabel($caption)->for(null);
	}


	/**
	 * @return Html
	 */
	public function getControlPart($key = null)
	{
		$key = key([(string) $key => null]);
		return parent::getControl()->addAttributes([
			'id' => $this->getHtmlId() . '-' . $key,
			'checked' => in_array($key, (array) $this->value, true),
			'disabled' => is_array($this->disabled) ? isset($this->disabled[$key]) : $this->disabled,
			'required' => null,
			'value' => $key,
		]);
	}


	/**
	 * @return Html
	 */
	public function getLabelPart($key = null)
	{
		$itemLabel = $this->itemLabel ? clone $this->itemLabel : clone $this->label;
		return func_num_args()
			? $itemLabel->setText($this->translate($this->items[$key]))->for($this->getHtmlId() . '-' . $key)
			: $this->getLabel();
	}


	/**
	 * Returns separator HTML element template.
	 * @return Html
	 */
	public function getSeparatorPrototype()
	{
		return $this->separator;
	}


	/**
	 * Returns container HTML element template.
	 * @return Html
	 */
	public function getContainerPrototype()
	{
		return $this->container;
	}


	/**
	 * Returns item label HTML element template.
	 * @return Html
	 */
	public function getItemLabelPrototype()
	{
		return $this->itemLabel ?: $this->itemLabel = Html::el('label');
	}
}
