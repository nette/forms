<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette\Forms\Form;


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
		$value = $this->getSubmittedValue(Form::DataList | Form::DataText);
		$this->value = $value
			? [(int) $value[0], (int) ($value[1] ?? 0)]
			: null;

		if ($this->value) {
			$this->getForm()->setSubmittedBy($this);
		}
	}


	public function getHtmlName(): string
	{
		return parent::getHtmlName() . '[]';
	}
}
