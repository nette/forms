<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms\{Form, Validation};
use Stringable;
use function in_array;


/**
 * Single line text input control.
 */
class TextInput extends TextBase
{
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


	public function getControl(): Nette\Utils\Html
	{
		return parent::getControl()->addAttributes([
			'value' => $this->control->type === 'password' ? $this->control->value : $this->getRenderedValue(),
			'type' => $this->control->type ?? 'text',
		]);
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
