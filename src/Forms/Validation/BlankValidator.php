<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Forms\Control;


/**
 * Validates that the control is left blank. Blankness is a control state,
 * not a value property, so the engine evaluates this validator directly.
 */
final class BlankValidator implements Validator
{
	public const NotBlank = 'forms.blank';


	public function validateControl(Control $control): ?Message
	{
		return $control->isFilled()
			? $this->createMessage()
			: null;
	}


	public function getClientRules(): array
	{
		return [new ClientRule(':blank', message: $this->createMessage())];
	}


	private function createMessage(): Message
	{
		return new Message(self::NotBlank, 'This field should be blank.');
	}
}
