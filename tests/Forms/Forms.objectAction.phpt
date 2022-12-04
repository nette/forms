<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


Assert::noError(function () {
	$form = new Form;
	$form->setMethod($form::Get);
	$form->setAction(new class {
		public function __toString(): string
		{
			return '/some/link';
		}
	});

	$form->render();
});
