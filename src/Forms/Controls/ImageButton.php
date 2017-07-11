<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;


/**
 * Submittable image button form control.
 */
class ImageButton extends SubmitButton
{

	/**
	 * @param  string  URI of the image
	 * @param  string  alternate text for the image
	 */
	public function __construct($src = null, $alt = null)
	{
		parent::__construct();
		$this->control->type = 'image';
		$this->control->src = $src;
		$this->control->alt = $alt;
	}


	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		parent::loadHttpData();
		$this->value = $this->value
			? [(int) array_shift($this->value), (int) array_shift($this->value)]
			: null;
	}


	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName()
	{
		return parent::getHtmlName() . '[]';
	}
}
