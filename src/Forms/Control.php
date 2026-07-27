<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use Nette\Utils\Html;
use Stringable;


/**
 * Contract for all form controls: everything the framework needs for validation,
 * rules, error handling and rendering. BaseControl implements it all; custom
 * implementations must provide the same surface to participate in a form.
 */
interface Control extends Nette\ComponentModel\IComponent
{
	/********************* value & state ****************d*g**/
	function setValue(mixed $value): static;

	function getValue(): mixed;

	/**
	 * Loads the value from the submitted HTTP data.
	 */
	function loadHttpData(): void;

	/**
	 * Is the control filled in?
	 */
	function isFilled(): bool;

	function isDisabled(): bool;

	/**
	 * Is control value excluded from $form->getValues() result?
	 */
	function isOmitted(): bool;

	/********************* validation ****************d*g**/

	function getRules(): Rules;

	function validate(): void;

	function addError(string|Stringable $message, bool $translate = true): void;

	/**
	 * Returns errors corresponding to control.
	 * @return list<string|Stringable>
	 */
	function getErrors(): array;

	/********************* form & rendering ****************d*g**/

	/**
	 * @return ($throw is true ? Form : ?Form)
	 */
	function getForm(bool $throw = true): ?Form;

	/**
	 * Returns HTML name of the control.
	 */
	function getHtmlName(): string;

	/**
	 * Generates control's HTML element.
	 */
	function getControl(): Html|string;

	/**
	 * Generates label's HTML element.
	 */
	function getLabel(string|Stringable|null $caption = null): Html|string|null;

	function setOption(string $key, mixed $value): static;

	function getOption(string $key): mixed;
}
