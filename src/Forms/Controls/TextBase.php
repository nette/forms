<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;
use Nette\Utils\Strings;
use Stringable;
use function is_scalar, sprintf;


/**
 * Base for text-based controls (TextInput, TextArea) with nullable and empty-value support.
 */
abstract class TextBase extends BaseControl
{
	protected string $emptyValue = '';
	protected mixed $rawValue = '';
	private bool $nullable = false;


	/** @internal */
	public function setValue(mixed $value): static
	{
		if ($value === null) {
			$value = '';
		} elseif (!is_scalar($value) && !$value instanceof Stringable) {
			throw new Nette\InvalidArgumentException(sprintf(
				"Value must be scalar or null, %s given in field '%s'.",
				get_debug_type($value),
				$this->getName(),
			));
		}

		$this->value = $value;
		$this->rawValue = (string) $value;
		return $this;
	}


	/**
	 * Returns the value, substituting empty string when it matches the empty value. Returns null when nullable is set and value is empty.
	 */
	public function getValue(): mixed
	{
		$value = $this->value === Strings::trim((string) $this->translate($this->emptyValue))
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


	public function isNullable(): bool
	{
		return $this->nullable;
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
	public function setMaxLength(?int $length): static
	{
		$this->control->maxlength = $length;
		return $this;
	}


	/**
	 * Validates the control, including the maxlength attribute the browser enforces.
	 */
	public function validate(): void
	{
		parent::validate();
		$max = $this->control->maxlength;
		if ($max !== null && !$this->isDisabled() && !$this->hasErrors()) {
			$failure = (new Nette\Forms\Validation\LengthValidator(max: (int) $max))->validate($this->rawValue);
			if ($failure) {
				$this->addError(Nette\Forms\MessageFormatter::formatStandalone($failure, $this->getTranslator()), translate: false);
			}
		}
	}


	public function getControl(): Nette\Utils\Html
	{
		$el = parent::getControl();
		if ($this->emptyValue !== '') {
			$el->attrs['data-nette-empty-value'] = Strings::trim((string) $this->translate($this->emptyValue));
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


	protected function getRuleAttributeNames(): array
	{
		return ['maxlength'];
	}
}
