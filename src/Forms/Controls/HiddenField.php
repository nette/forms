<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Utils\Html;
use Stringable;


/**
 * Hidden form control used to store a non-displayed value.
 */
class HiddenField extends BaseControl
{
	private bool $persistValue = false;
	private bool $nullable = false;


	public function __construct($persistentValue = null)
	{
		parent::__construct();
		$this->control->type = 'hidden';
		$this->setOption('type', 'hidden');
		$this->value = '';
		if ($persistentValue !== null) {
			$this->unmonitor(Nette\Forms\Form::class);
			$this->persistValue = true;
			$this->value = (string) $persistentValue;
		}
	}


	/**
	 * Sets control's value.
	 * @return static
	 * @internal
	 */
	public function setValue($value)
	{
		if ($value === null) {
			$value = '';
		} elseif ($value instanceof \BackedEnum) {
			$value = $value->value;
		} elseif (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
			throw new Nette\InvalidArgumentException(sprintf("Value must be scalar or null, %s given in field '%s'.", gettype($value), $this->name));
		}

		if (!$this->persistValue) {
			$this->value = $value;
		}

		return $this;
	}


	public function getValue(): mixed
	{
		return $this->nullable && $this->value === '' ? null : $this->value;
	}


	/**
	 * Sets whether getValue() returns null instead of empty string.
	 */
	public function setNullable(bool $value = true): static
	{
		$this->nullable = $value;
		return $this;
	}


	public function getControl(): Html
	{
		$this->setOption('rendered', true);
		$el = clone $this->control;
		return $el->addAttributes([
			'name' => $this->getHtmlName(),
			'disabled' => $this->isDisabled(),
			'value' => (string) $this->value,
		]);
	}


	/**
	 * Bypasses label generation.
	 */
	public function getLabel($caption = null): Html|string|null
	{
		return null;
	}


	/**
	 * Adds error message to the list.
	 */
	public function addError(string|Stringable $message, bool $translate = true): void
	{
		$this->getForm()->addError($message, $translate);
	}
}
