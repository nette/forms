<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Stringable;
use function in_array;


/**
 * Single line text input control.
 */
class TextInput extends TextBase
{
	/** @var array<int|string, string|Stringable> */
	private array $suggestions = [];


	public function __construct(string|Stringable|null $label = null, ?int $maxLength = null)
	{
		parent::__construct($label);
		$this->control->maxlength = $maxLength;
		$this->setOption('type', 'text');
	}


	public function loadHttpData(): void
	{
		$this->setValue($this->submittedText = $this->getSubmittedValue(Form::DataLine));
	}


	/**
	 * Changes control's type attribute.
	 */
	public function setHtmlType(string $type): static
	{
		$this->control->type = $type;
		return $this;
	}


	#[\Deprecated('use setHtmlType()', since: '3.0')]
	public function setType(string $type): static
	{
		return $this->setHtmlType($type);
	}


	/**
	 * Sets autocompletion suggestions rendered as a <datalist>. A list of values,
	 * or value => label pairs (any array that is not a list); labels are translated, values are not.
	 * @param  array<int|string, string|Stringable>  $items
	 */
	public function setSuggestions(array $items): static
	{
		$this->suggestions = $items;
		return $this;
	}


	public function getControl(): Nette\Utils\Html
	{
		$input = parent::getControl();
		$input->addAttributes([
			'value' => $input->type === 'password' ? $this->control->value : $this->getRenderedValue(),
			'type' => $input->type ?? 'text',
		]);
		if ($this->suggestions) {
			$input->list ??= $this->getHtmlId() . '-list';
		}

		return $input;
	}


	/**
	 * Returns the <datalist> with the suggestions, or null when there are none.
	 * Renderers emit it before the form end, like unrendered hidden fields.
	 */
	public function getDatalist(): ?Html
	{
		if (!$this->suggestions) {
			return null;
		}

		$datalist = Html::el('datalist')->id($this->control->list ?? $this->getHtmlId() . '-list');
		$isList = array_is_list($this->suggestions);
		foreach ($this->suggestions as $value => $label) {
			$datalist->addHtml(
				$isList
					? Html::el('option')->value((string) $label)
					: Html::el('option')->value((string) $value)->label((string) $this->translate($label)),
			);
		}

		return $datalist;
	}


	protected function getRuleAttributeNames(): array
	{
		// the type set by setHtmlType() wins over the type the rules declare
		$type = $this->control->type ?? $this->getRules()->getHtmlAttributes()['type'] ?? 'text';
		$names = [...parent::getRuleAttributeNames(), 'type'];
		if (in_array($type, ['number', 'range', 'datetime-local', 'datetime', 'date', 'month', 'week', 'time'], true)) {
			$names[] = 'min';
			$names[] = 'max';
		}

		if (in_array($type, ['text', 'search', 'tel', 'url', 'email', 'password'], true)) {
			$names[] = 'pattern';
		}

		if ($type === 'text') {
			$names[] = 'inputmode';
		}

		return $names;
	}
}
