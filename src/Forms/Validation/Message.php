<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use function is_scalar, strlen;


/**
 * Validation failure message carrying a stable identifier, a default English template
 * and data discovered by the validator during validation.
 */
final readonly class Message implements \Stringable
{
	public function __construct(
		public string $id,
		public string|\Stringable $template,
		/** @var array<string, mixed> */
		public array $parameters = [],
		public ?int $count = null,
	) {
	}


	/**
	 * Returns a copy with the template replaced.
	 */
	public function withTemplate(string|\Stringable $template): self
	{
		return new self($this->id, $template, $this->parameters, $this->count);
	}


	/**
	 * Substitutes the message parameters (longer names first, so %min does not eat
	 * %minLength) into the given template; %label and %value are left for the
	 * message renderer, which knows the control. The optional $coerce callback
	 * pre-processes each value; non-stringable results are skipped.
	 * @param  ?callable(mixed): mixed  $coerce
	 */
	public function substitute(string|\Stringable $template, ?callable $coerce = null): string
	{
		$s = (string) $template;
		$parameters = $this->parameters;
		if ($this->count !== null) {
			$parameters['count'] ??= $this->count;
		}

		uksort($parameters, fn(string $a, string $b): int => strlen($b) <=> strlen($a));
		foreach ($parameters as $name => $value) {
			$value = $coerce ? $coerce($value) : $value;
			$value = $value instanceof \BackedEnum ? $value->value : $value;
			if (is_scalar($value) || $value instanceof \Stringable) {
				$s = str_replace('%' . $name, (string) $value, $s);
			}
		}

		return $s;
	}


	/**
	 * Returns the template with its placeholders, so a translator keyed by the English
	 * text finds it; the parameters are substituted after translation.
	 */
	public function __toString(): string
	{
		return (string) $this->template;
	}
}
