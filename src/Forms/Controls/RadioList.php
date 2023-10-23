<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

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
	public bool $generateId = false;
	protected Html $separator;
	protected Html $container;
	protected Html $itemLabel;


	/**
	 * @param  string|object  $label
	 */
	public function __construct($label = null, ?array $items = null)
	{
		parent::__construct($label, $items);
		$this->control->type = 'radio';
		$this->container = Html::el();
		$this->separator = Html::el('br');
		$this->itemLabel = Html::el('label');
		$this->setOption('type', 'radio');
	}


	public function getControl(): Html
	{
		$input = parent::getControl();
		$items = $this->getItems();
		$ids = [];
		if ($this->generateId) {
			foreach ($items as $value => $label) {
				$ids[$value] = $input->id . '-' . $value;
			}
		}

		return $this->container->setHtml(
			Nette\Forms\Helpers::createInputList(
				$this->translate($items),
				array_merge($input->attrs, [
					'id:' => $ids,
					'checked?' => $this->value,
					'disabled:' => $this->disabled,
					'data-nette-rules:' => [key($items) => $input->attrs['data-nette-rules']],
				]),
				['for:' => $ids] + $this->itemLabel->attrs,
				$this->separator,
			),
		);
	}


	public function getLabel($caption = null): Html
	{
		return parent::getLabel($caption)->for(null);
	}


	public function getControlPart($key = null): Html
	{
		$key = key([(string) $key => null]);
		return parent::getControl()->addAttributes([
			'id' => $this->getHtmlId() . '-' . $key,
			'checked' => in_array($key, (array) $this->value, strict: true),
			'disabled' => is_array($this->disabled) ? isset($this->disabled[$key]) : $this->disabled,
			'value' => $key,
		]);
	}


	public function getLabelPart($key = null): Html
	{
		$itemLabel = clone $this->itemLabel;
		return func_num_args()
			? $itemLabel->setText($this->translate($this->items[$key]))->for($this->getHtmlId() . '-' . $key)
			: $this->getLabel();
	}


	/**
	 * Returns separator HTML element template.
	 */
	public function getSeparatorPrototype(): Html
	{
		return $this->separator;
	}


	/**
	 * Returns container HTML element template.
	 */
	public function getContainerPrototype(): Html
	{
		return $this->container;
	}


	/**
	 * Returns item label HTML element template.
	 */
	public function getItemLabelPrototype(): Html
	{
		return $this->itemLabel;
	}
}
