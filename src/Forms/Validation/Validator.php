<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;


/**
 * Common contract of validator objects. Most validators inspect the value alone
 * and implement ValueValidator; the filled/blank checks inspect the control state
 * and are recognized by the engine directly.
 */
interface Validator
{
	/**
	 * Returns client-side counterparts of the validator for the netteForms.js
	 * script. An empty list means the validator runs on the server only.
	 * @return list<ClientRule>
	 */
	function getClientRules(): array;
}
