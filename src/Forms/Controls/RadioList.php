<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;
use Nette\Utils\Html;
use Stringable;
use function array_key_exists, array_key_first, array_merge, func_num_args, in_array, is_array, key;


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


	/** @param  ?mixed[]  $items */
	public function __construct(string|Stringable|null $label = null, ?array $items = null)
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
		if (!$items) {
			return Html::el();
		}
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
					'data-nette-rules:' => [array_key_first($items) => $input->attrs['data-nette-rules']],
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


	/**
	 * Returns the HTML input element for a specific radio button item by key.
	 */
	public function getControlPart($key = null): Html
	{
		$key = key([(string) $key => null]);
		if (!array_key_exists($key, $this->getItems())) {
			throw new Nette\InvalidArgumentException("Item '$key' does not exist in field '{$this->getName()}'.");
		}

		return parent::getControl()->addAttributes([
			'id' => $this->getHtmlId() . '-' . $key,
			'checked' => in_array($key, (array) $this->value, strict: true),
			'disabled' => is_array($this->disabled) ? isset($this->disabled[$key]) : $this->disabled,
			'value' => $key,
		]);
	}


	/**
	 * Returns the label element for the whole radio list, or the item label for a specific key.
	 */
	public function getLabelPart($key = null): Html
	{
		if (!func_num_args()) {
			return $this->getLabel();
		}

		$key = key([(string) $key => null]);
		if (!array_key_exists($key, $this->getItems())) {
			throw new Nette\InvalidArgumentException("Item '$key' does not exist in field '{$this->getName()}'.");
		}

		$itemLabel = clone $this->itemLabel;
		return $itemLabel->setText($this->translate($this->getItems()[$key]))->for($this->getHtmlId() . '-' . $key);
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
