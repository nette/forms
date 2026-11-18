<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Forms\Control;


/**
 * Validates that the control is filled in. Filledness is a control state,
 * not a value property.
 */
final class FilledValidator implements ControlValidator
{
	public const Required = 'forms.filled';


	public function validateControl(Control $control): ?Message
	{
		return $control->isFilled()
			? null
			: $this->createMessage();
	}


	public function getClientRule(): ClientRule
	{
		return new ClientRule(':filled', message: $this->createMessage());
	}


	private function createMessage(): Message
	{
		return new Message(self::Required, 'This field is required.');
	}
}
