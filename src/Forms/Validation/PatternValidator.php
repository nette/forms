<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Http\FileUpload;
use Nette\Utils\Strings;
use function is_object;


/**
 * Validates that the value (or the names of uploaded files) matches
 * the regular expression. The pattern is anchored to the whole value.
 */
final readonly class PatternValidator implements ValueValidator
{
	public const Mismatch = 'forms.pattern';


	public function __construct(
		public string $pattern,
		public bool $caseInsensitive = false,
	) {
	}


	public function validate(mixed $value): ?Message
	{
		$regexp = "\x01^(?:{$this->pattern})$\x01Du" . ($this->caseInsensitive ? 'i' : '');
		foreach (is_object($value) ? [$value] : (array) $value as $item) {
			$item = $item instanceof FileUpload ? $item->getUntrustedName() : $item;
			if (!Strings::match((string) $item, $regexp)) {
				return $this->createMessage();
			}
		}

		return null;
	}


	public function getClientRules(): array
	{
		return [new ClientRule(
			$this->caseInsensitive ? ':patternCaseInsensitive' : ':pattern',
			arg: $this->pattern,
			message: $this->createMessage(),
			attributes: $this->caseInsensitive ? [] : ['pattern' => $this->pattern],
		)];
	}


	private function createMessage(): Message
	{
		return new Message(self::Mismatch, 'Please enter a value in the required format.');
	}
}
