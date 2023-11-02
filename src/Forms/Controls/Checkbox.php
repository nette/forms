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
 * Check box control. Allows the user to select a true or false condition.
 */
class Checkbox extends BaseControl
{
	private Html $container;


	public function __construct(string|Stringable|null $label = null)
	{
		parent::__construct($label);
		$this->control->type = 'checkbox';
		$this->container = Html::el();
		$this->setOption('type', 'checkbox');
	}


	/**
	 * Sets control's value.
	 * @return static
	 * @internal
	 */
	public function setValue($value)
	{
		if (!is_scalar($value) && $value !== null) {
			throw new Nette\InvalidArgumentException(sprintf("Value must be scalar or null, %s given in field '%s'.", get_debug_type($value), $this->name));
		}

		$this->value = (bool) $value;
		return $this;
	}


	public function isFilled(): bool
	{
		return $this->getValue() !== false; // back compatibility
	}


	public function getControl(): Html
	{
		return $this->container->setHtml($this->getLabelPart()->insert(0, $this->getControlPart()));
	}


	/**
	 * Bypasses label generation.
	 */
	public function getLabel($caption = null): Html|string|null
	{
		return null;
	}


	public function getControlPart(): Html
	{
		return parent::getControl()->checked($this->value);
	}


	public function getLabelPart(): Html
	{
		return parent::getLabel();
	}


	/**
	 * Returns container HTML element template.
	 */
	public function getContainerPrototype(): Html
	{
		return $this->container;
	}


	/** @deprecated  use getContainerPrototype() */
	public function getSeparatorPrototype(): Html
	{
		return $this->container;
	}
}
