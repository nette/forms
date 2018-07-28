<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


Nette\Forms\Container::extensionMethod('test', function ($form, $a, $b) {
	Assert::type(Nette\Forms\Form::class, $form);
	Assert::same(1, $a);
	Assert::same(2, $b);
	return 3;
});

$form = new Form;
Assert::same(3, $form->test(1, 2));
