<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;


/**
 * Color picker.
 */
class ColorPicker extends BaseControl
{
	public function __construct($label = null)
	{
		parent::__construct($label);
		$this->setOption('type', 'color');
	}


	/**
	 * @param  ?string $value
	 */
	public function setValue($value): static
	{
		if ($value === null) {
			$this->value = '#000000';
		} elseif (is_string($value) && preg_match('~#?[0-9a-f]{6}~DAi', $value)) {
			$this->value = '#' . strtolower(ltrim($value, '#'));
		} else {
			throw new Nette\InvalidArgumentException('Color must have #rrggbb format.');
		}
		return $this;
	}


	public function loadHttpData(): void
	{
		try {
			parent::loadHttpData();
		} catch (Nette\InvalidArgumentException $e) {
			$this->setValue(null);
		}
	}


	public function getControl(): Nette\Utils\Html
	{
		return parent::getControl()->addAttributes([
			'type' => 'color',
			'value' => $this->value,
		]);
	}
}
