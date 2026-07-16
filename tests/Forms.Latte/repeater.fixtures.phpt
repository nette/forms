<?php declare(strict_types=1);

/**
 * The HTML fixtures of the end-to-end repeater specs match what PHP renders now.
 */

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../netteForms/fixtures/fixtures.php';


foreach (createRepeaterFixtures() as $file => $html) {
	Assert::same(
		str_replace("\r\n", "\n", file_get_contents(__DIR__ . '/../netteForms/fixtures/' . $file)),
		str_replace("\r\n", "\n", $html),
		"$file is outdated, run php tests/netteForms/fixtures/generate.php",
	);
}
