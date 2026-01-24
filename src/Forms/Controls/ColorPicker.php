<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;
use function is_string, ltrim, preg_match, strtolower;


/**
 * Color picker returning a hex color string (e.g. '#336699').
 */
class ColorPicker extends BaseControl
{
	public function __construct(string|\Stringable|null $label = null)
	{
		parent::__construct($label);
		$this->setOption('type', 'color');
	}


	/**
	 * Sets the color value in #rrggbb format. Null resets to black (#000000).
	 * @param  ?string  $value
	 */
	public function setValue($value): static
	{
		if ($value === null) {
			$this->value = '#000000';
		} elseif (is_string($value) && preg_match('~#?[0-9a-f]{6}~Ai', $value)) {
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
		} catch (Nette\InvalidArgumentException) {
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
