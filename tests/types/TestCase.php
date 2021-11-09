<?php

declare(strict_types=1);

namespace PHPUnit\Framework;

use Tester\Assert;


abstract class TestCase extends \Tester\TestCase
{
	protected function assertSame(mixed $expected, mixed $actual, string $message = ''): void
	{
		Assert::same($expected, $actual, $message);
	}


	protected function assertTrue(mixed $actual, string $message = ''): void
	{
		Assert::true($actual, $message);
	}


	protected function assertInstanceOf(string $expected, object $actual): void
	{
		Assert::type($expected, $actual);
	}
}
