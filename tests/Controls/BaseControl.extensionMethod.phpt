<?php

declare(strict_types=1);

use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\TextBase;
use Nette\Forms\Controls\TextInput;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


TextBase::extensionMethod('test', function ($control, $a, $b) {
	Assert::type(TextInput::class, $control);
	Assert::same(1, $a);
	Assert::same(2, $b);
	return 'TextInput';
});

Checkbox::extensionMethod('test', function ($control, $a, $b) {
	Assert::type(Checkbox::class, $control);
	Assert::same(1, $a);
	Assert::same(2, $b);
	return 'Checkbox';
});

$control1 = new TextInput;
Assert::same('TextInput', $control1->test(1, 2));

$control2 = new Checkbox;
Assert::same('Checkbox', $control2->test(1, 2));

Assert::exception(function () {
	$control3 = new Button;
	$control3->test(1, 2);
}, Nette\MemberAccessException::class);
