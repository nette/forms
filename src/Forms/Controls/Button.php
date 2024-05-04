<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette\Utils\Html;
use Stringable;


/**
 * Push button control with no default behavior.
 */
class Button extends BaseControl
{
	public function __construct(string|Stringable|null $caption = null)
	{
		parent::__construct($caption);
		$this->control->type = 'button';
		$this->setOption('type', 'button');
		$this->setOmitted();
	}


	/**
	 * Is button pressed?
	 */
	public function isFilled(): bool
	{
		$value = $this->getValue();
		return $value !== null && $value !== [];
	}


	/**
	 * Bypasses label generation.
	 */
	public function getLabel($caption = null): Html|string|null
	{
		return null;
	}


	public function renderAsButton(bool $state = true): static
	{
		$this->control->setName($state ? 'button' : 'input');
		return $this;
	}


	/**
	 * Generates control's HTML element.
	 */
	public function getControl(string|Stringable|null $caption = null): Html
	{
		$this->setOption('rendered', true);
		$caption = $this->translate($caption ?? $this->getCaption());
		$el = (clone $this->control)->addAttributes([
			'name' => $this->getHtmlName(),
			'disabled' => $this->isDisabled(),
		]);
		if ($caption instanceof Html || ($caption !== null && $el->getName() === 'button')) {
			$el->setName('button')->setText($caption);
		} else {
			$el->value = $caption;
		}

		return $el;
	}
}
