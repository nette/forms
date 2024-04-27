<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;


/**
 * Submittable image button form control.
 */
class ImageButton extends SubmitButton
{
	/**
	 * @param  ?string  $src  URI of the image
	 * @param  ?string  $alt  alternate text for the image
	 */
	public function __construct(?string $src = null, ?string $alt = null)
	{
		parent::__construct();
		$this->control->type = 'image';
		$this->control->src = $src;
		$this->control->alt = $alt;
	}


	public function loadHttpData(): void
	{
		parent::loadHttpData();
		$this->value = $this->value
			? [(int) array_shift($this->value), (int) array_shift($this->value)]
			: null;
	}


	public function getHtmlName(): string
	{
		return parent::getHtmlName() . '[]';
	}
}
