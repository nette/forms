<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;


/**
 * Contract for all form controls.
 */
interface Control
{
	/**
	 * @param  mixed  $value
	 * @return static
	 */
	function setValue(mixed $value);

	/** @return mixed */
	function getValue();

	function validate(): void;

	/**
	 * Returns errors corresponding to control.
	 * @return list<string|\Stringable>
	 */
	function getErrors(): array;

	/**
	 * Is control value excluded from $form->getValues() result?
	 */
	function isOmitted(): bool;
}
