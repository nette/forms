<?php

/**
 * Test: Nette\Forms\Helpers::generateHtmlName()
 */

declare(strict_types=1);

use Nette\Forms\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('HTML-friendly name generation from form controls', function () {
	Assert::same('name', Helpers::generateHtmlName('name'));
	Assert::same('first[name]', Helpers::generateHtmlName('first-name'));
	Assert::same('first[second][name]', Helpers::generateHtmlName('first-second-name'));
	Assert::same('_submit', Helpers::generateHtmlName('submit'));
});
