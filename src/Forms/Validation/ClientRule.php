<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;


/**
 * Describes a single client-side rule for the netteForms.js validation script.
 */
final readonly class ClientRule
{
	public function __construct(
		/** validator name known to Nette.validators, e.g. ':email' */
		public string $operation,
		public mixed $arg = null,
		/** client-safe message template (must not rely on data discovered on the server) */
		public ?Message $message = null,
		/** @var array<string, mixed> HTML validation attributes such as maxlength, min, max or accept */
		public array $attributes = [],
	) {
	}
}
