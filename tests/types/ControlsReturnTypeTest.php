<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/TestCase.php';


class ControlsReturnTypeTest extends PHPStan\Testing\TypeInferenceTestCase
{
	/** @return iterable<mixed> */
	public function dataFileAsserts(): iterable
	{
		yield from $this->gatherAssertTypes(__DIR__ . '/data/Controls.getValue().php');
	}


	/** @dataProvider dataFileAsserts */
	public function testFileAsserts(string $assertType, string $file, mixed ...$args): void
	{
		$this->assertFileAsserts($assertType, $file, ...$args);
	}
}


$testCase = new ControlsReturnTypeTest;
$testCase->run();
