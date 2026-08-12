<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Utils\Validators;
use function is_scalar;


/**
 * Validates that the value is an absolute URL and auto-prepends the https://
 * scheme when it is missing.
 */
final class UrlValidator implements ValueValidator, Filter
{
	public const NotUrl = 'forms.url';


	public function validate(mixed $value): ?Message
	{
		$value = is_scalar($value) || $value instanceof \Stringable ? (string) $value : '';
		return Validators::isUrl($value) || Validators::isUrl("https://$value")
			? null
			: $this->createMessage();
	}


	public function filter(mixed $value): mixed
	{
		$value = (string) $value;
		return Validators::isUrl($value) ? $value : "https://$value";
	}


	public function getClientRules(): array
	{
		// the client-side counterpart auto-prepends the scheme the same way
		return [new ClientRule(':url', message: $this->createMessage())];
	}


	private function createMessage(): Message
	{
		return new Message(self::NotUrl, 'Please enter a valid URL.');
	}
}
