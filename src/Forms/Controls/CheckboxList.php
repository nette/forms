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
 */
class CheckboxList extends MultiChoiceControl
{
	/** @var Html  separator element template */
	protected $separator;

	/** @var Html  container element template */
	protected $container;


	/**
	 * @param  string  label
	 * @param  array   options from which to choose
	 */
	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);
		$this->control->type = 'checkbox';
		$this->container = Html::el();
		$this->separator = Html::el('br');
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
					'id' => NULL,
					'checked?' => $this->value,
					'disabled:' => $this->disabled,
					'required' => NULL,
					'data-nette-rules:' => [key($items) => $input->attrs['data-nette-rules']],
				]),
				$this->label->attrs,
				$this->separator
			)
		);
	}


	/**
	 * Generates label's HTML element.
	 * @param  string
	 * @return Html
	 */
	public function getLabel($caption = NULL)
	{
		return parent::getLabel($caption)->for(NULL);
	}


	/**
	 * @return Html
	 */
	public function getControlPart($key = NULL)
	{
		$key = key([(string) $key => NULL]);
		return parent::getControl()->addAttributes([
			'id' => $this->getHtmlId() . '-' . $key,
			'checked' => in_array($key, (array) $this->value, TRUE),
			'disabled' => is_array($this->disabled) ? isset($this->disabled[$key]) : $this->disabled,
			'required' => NULL,
			'value' => $key,
		]);
	}


	/**
	 * @return Html
	 */
	public function getLabelPart($key = NULL)
	{
		return func_num_args()
			? parent::getLabel($this->items[$key])->for($this->getHtmlId() . '-' . $key)
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

}
