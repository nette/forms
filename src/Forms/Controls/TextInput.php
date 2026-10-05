<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms\{Form, Validation};
use Nette\Utils\Html;
use Stringable;
use function in_array, is_int;


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
		$this->setValue($this->getSubmittedValue(Form::DataLine));
	}


	/**
	 * Changes control's type attribute.
	 */
	public function setHtmlType(string $type): static
	{
		$this->control->type = $type;
		return $this;
	}


	#[\Deprecated('use setHtmlType()')]
	public function setType(string $type): static
	{
		trigger_error(__METHOD__ . '() was renamed to setHtmlType()', E_USER_DEPRECATED);
		return $this->setHtmlType($type);
	}


	/**
	 * Sets autocompletion suggestions rendered as a <datalist>. A list of values,
	 * or value => label pairs; labels are translated.
	 * @param  array<int|string, string|Stringable>  $items
	 */
	public function setSuggestions(array $items): static
	{
		$this->suggestions = $items;
		return $this;
	}


	public function getControl(): Nette\Utils\Html
	{
		$input = parent::getControl()->addAttributes([
			'value' => $this->control->type === 'password' ? $this->control->value : $this->getRenderedValue(),
			'type' => $this->control->type ?? 'text',
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
		foreach ($this->suggestions as $value => $label) {
			$datalist->addHtml(
				is_int($value)
					? Html::el('option')->value((string) $this->translate($label))
					: Html::el('option')->value($value)->label((string) $this->translate($label)),
			);
		}

		return $datalist;
	}


	/** @param  Validation\Validator|(callable(never, never): bool)|string  $validator */
	public function addRule(
		Validation\Validator|callable|string $validator,
		string|Stringable|null $errorMessage = null,
		mixed $arg = null,
	): static
	{
		if ($this->getRules()->containsMutating()) {
			// the value changes on the server, so HTML attributes must not pre-validate the raw input
			return parent::addRule($validator, $errorMessage, $arg);
		}

		$type = match (true) {
			$validator === Form::Email, $validator instanceof Validation\EmailValidator => 'email',
			$validator === Form::URL, $validator instanceof Validation\UrlValidator => 'url',
			$validator === Form::Integer, $validator instanceof Validation\IntegerValidator => 'number',
			default => null,
		};
		if ($type !== null && $this->control->type === null) {
			$this->control->type = $type;
		}

		// only a keyboard hint, type=number would reject decimal commas the server accepts
		if (
			($validator === Form::Float || $validator instanceof Validation\FloatValidator)
			&& $this->control->type === null
		) {
			$this->control->inputmode ??= 'decimal';
		}

		return parent::addRule($validator, $errorMessage, $arg);
	}


	protected function getRuleAttributeNames(): array
	{
		$names = parent::getRuleAttributeNames();
		if (in_array($this->control->type, ['number', 'range', 'datetime-local', 'datetime', 'date', 'month', 'week', 'time'], true)) {
			$names[] = 'min';
			$names[] = 'max';
		}

		if (in_array($this->control->type, [null, 'text', 'search', 'tel', 'url', 'email', 'password'], true)) {
			$names[] = 'pattern';
		}

		return $names;
	}
}
