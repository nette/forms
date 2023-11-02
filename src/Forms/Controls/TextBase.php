<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms\Form;
use Nette\Utils\Strings;
use Stringable;


/**
 * Implements the basic functionality common to text input controls.
 */
abstract class TextBase extends BaseControl
{
	protected string $emptyValue = '';
	protected mixed $rawValue = '';
	private bool $nullable = false;


	/**
	 * Sets control's value.
	 * @return static
	 * @internal
	 */
	public function setValue($value)
	{
		if ($value === null) {
			$value = '';
		} elseif (!is_scalar($value) && !$value instanceof Stringable) {
			throw new Nette\InvalidArgumentException(sprintf("Value must be scalar or null, %s given in field '%s'.", get_debug_type($value), $this->name));
		}

		$this->value = $value;
		$this->rawValue = (string) $value;
		return $this;
	}


	/**
	 * Returns control's value.
	 * @return mixed
	 */
	public function getValue(): mixed
	{
		$value = $this->value === Strings::trim($this->translate($this->emptyValue))
			? ''
			: $this->value;
		return $this->nullable && $value === '' ? null : $value;
	}


	/**
	 * Sets whether getValue() returns null instead of empty string.
	 */
	public function setNullable(bool $value = true): static
	{
		$this->nullable = $value;
		return $this;
	}


	/**
	 * Sets the special value which is treated as empty string.
	 */
	public function setEmptyValue(string $value): static
	{
		$this->emptyValue = $value;
		return $this;
	}


	/**
	 * Returns the special value which is treated as empty string.
	 */
	public function getEmptyValue(): string
	{
		return $this->emptyValue;
	}


	/**
	 * Sets the maximum number of allowed characters.
	 */
	public function setMaxLength(int $length): static
	{
		$this->control->maxlength = $length;
		return $this;
	}


	public function getControl(): Nette\Utils\Html
	{
		$el = parent::getControl();
		if ($this->emptyValue !== '') {
			$el->attrs['data-nette-empty-value'] = Strings::trim($this->translate($this->emptyValue));
		}

		if (isset($el->placeholder)) {
			$el->placeholder = $this->translate($el->placeholder);
		}

		return $el;
	}


	protected function getRenderedValue(): ?string
	{
		return $this->rawValue === ''
			? ($this->emptyValue === '' ? null : $this->translate($this->emptyValue))
			: $this->rawValue;
	}


	/** @return static */
	public function addRule(
		callable|string $validator,
		string|Stringable|null $errorMessage = null,
		mixed $arg = null,
	) {
		foreach ($this->getRules() as $rule) {
			if (!$rule->canExport() && !$rule->branch) {
				return parent::addRule($validator, $errorMessage, $arg);
			}
		}

		if ($validator === Form::Length || $validator === Form::MaxLength) {
			$tmp = is_array($arg) ? $arg[1] : $arg;
			if (is_scalar($tmp)) {
				$this->control->maxlength = isset($this->control->maxlength)
					? min($this->control->maxlength, $tmp)
					: $tmp;
			}
		}

		return parent::addRule($validator, $errorMessage, $arg);
	}
}
