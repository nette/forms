<?php

/**
 * Test: A subclass that re-applies the Nette\SmartObject trait must remain
 * compatible with Container::__call(). The trait declares __call() without a
 * native return type, so Container::__call() must not declare one either,
 * otherwise PHP raises a fatal "incompatible declaration" error at compile time.
 * @see https://github.com/nette/utils/issues/338
 */

declare(strict_types=1);

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class FormWithSmartObject extends Nette\Forms\Form
{
	use Nette\SmartObject;
}


Assert::type(Nette\Forms\Form::class, new FormWithSmartObject);
