<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;
use Nette\Utils\Html;


/**
 * Set of radio button controls.
 *
 * @property-read Html $separatorPrototype
 * @property-read Html $containerPrototype
 * @property-read Html $itemLabelPrototype
 */
class RadioList extends ChoiceControl
{
	/** @var bool */
	public $generateId = FALSE;

	/** @var Html  separator element template */
	protected $separator;

	/** @var Html  container element template */
	protected $container;

	/** @var Html  item label template */
	protected $itemLabel;


	/**
	 * @param  string  label
	 * @param  array   options from which to choose
	 */
	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);
		$this->control->type = 'radio';
		$this->container = Html::el();
		$this->separator = Html::el('br');
		$this->itemLabel = Html::el();
	}


	/**
	 * Returns selected radio value.
	 * @return mixed
	 */
	public function getValue()
	{
		return parent::getValue();
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
		return $this->itemLabel;
	}


	/**
	 * Generates control's HTML element.
	 * @return Html
	 */
	public function getControl()
	{
		$input = parent::getControl();
		$items = $this->getItems();
		$ids = array();
		if ($this->generateId) {
			foreach ($items as $value => $label) {
				$ids[$value] = $input->id . '-' . $value;
			}
		}

		return $this->container->setHtml(
			Nette\Forms\Helpers::createInputList(
				$this->translate($items),
				array_merge($input->attrs, array(
					'id:' => $ids,
					'checked?' => $this->value,
					'disabled:' => $this->disabled,
					'data-nette-rules:' => array(key($items) => $input->attrs['data-nette-rules']),
				)),
				array('for:' => $ids) + $this->itemLabel->attrs,
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
	public function getControlPart($key)
	{
		$key = key(array((string) $key => NULL));
		return parent::getControl()->addAttributes(array(
			'id' => $this->getHtmlId() . '-' . $key,
			'checked' => in_array($key, (array) $this->value, TRUE),
			'disabled' => is_array($this->disabled) ? isset($this->disabled[$key]) : $this->disabled,
			'value' => $key,
		));
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

}
