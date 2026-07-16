<?php declare(strict_types=1);

// Regenerates the HTML fixtures of the end-to-end repeater specs: php tests/netteForms/fixtures/generate.php

require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/fixtures.php';

foreach (createRepeaterFixtures() as $file => $html) {
	file_put_contents(__DIR__ . '/' . $file, $html);
}
