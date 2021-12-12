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
	 * @param  string|object  $label
	 */
	public function __construct($label = null, ?array $items = null)
	{
		parent::__construct($label, $items);
		$this->control->type = 'checkbox';
		$this->container = Html::el();
		$this->separator = Html::el('br');
		$this->itemLabel = Html::el('label');
		$this->setOption('type', 'checkbox');
	}


	public function loadHttpData(): void
	{
		$data = $this->getForm()->getHttpData(Nette\Forms\Form::DATA_TEXT, substr($this->getHtmlName(), 0, -2));
		$data = $data === null
			? $this->getHttpData(Nette\Forms\Form::DATA_TEXT)
			: explode(',', $data);
		$this->value = array_keys(array_flip($data));
		if (is_array($this->disabled)) {
			$this->value = array_diff($this->value, array_keys($this->disabled));
		}
	}


	public function getControl(): Html
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
				$this->itemLabel->attrs,
				$this->separator
			)
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
			'checked' => in_array($key, (array) $this->value, true),
			'disabled' => is_array($this->disabled) ? isset($this->disabled[$key]) : $this->disabled,
			'required' => null,
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
