<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Forms\Control;


/**
 * Validates that the value equals none of the given values, the opposite of EqualValidator.
 */
final readonly class NotEqualValidator implements ValueValidator, ControlValidator
{
	public const Equal = 'forms.notEqual';

	private EqualValidator $equal;


	public function __construct(mixed $unexpected)
	{
		$this->equal = new EqualValidator($unexpected);
	}


	public function validate(mixed $value): ?Message
	{
		return $this->equal->validate($value) === null ? $this->createMessage() : null;
	}


	public function validateControl(Control $control): ?Message
	{
		return $this->equal->validateControl($control) === null ? $this->createMessage() : null;
	}


	public function getClientRule(): ClientRule
	{
		return new ClientRule(
			':notEqual',
			arg: $this->equal->expected,
			message: $this->createMessage($this->equal->describeExpected()),
		);
	}


	/** @param  ?list<mixed>  $unexpected */
	private function createMessage(?array $unexpected = null): Message
	{
		return new Message(
			self::Equal,
			'This value should not be %unexpected.',
			['unexpected' => $unexpected ?? $this->equal->resolveExpected()],
		);
	}
}
