<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms;


class ScalarValue
{

	/** @var string */
	private $scalarValue;

	/** @var mixed */
	private $mixedValue;


	public function __construct(string $scalarValue, $mixedValue)
	{
		$this->scalarValue = $scalarValue;
		$this->mixedValue = $mixedValue;
	}


	/**
	 * @return mixed
	 */
	public function getMixedValue()
	{
		return $this->mixedValue;
	}


	public function __toString(): string
	{
		return $this->scalarValue;
	}
}
