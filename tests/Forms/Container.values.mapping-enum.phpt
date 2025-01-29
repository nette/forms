<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


enum TestEnum: string
{
	case Case1 = 'case 1';
	case Case2 = 'case 2';
}

class FormWithEnum
{
	public function __construct(
		public TestEnum $enum1,
		public ?TestEnum $enum2,
	) {
	}
}


test('handling enum types in form data', function () {
	$form = new Form;
	$form->addText('enum1');
	$form->addText('enum2')->setNullable();
	$form->setValues(['enum1' => 'case 1', 'enum2' => null]);

	Assert::equal(new FormWithEnum(
		enum1: TestEnum::Case1,
		enum2: null,
	), $form->getValues(FormWithEnum::class));
});
