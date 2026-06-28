<?php declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


enum TestEnum: string
{
	case Case1 = 'case 1';
	case Case2 = 'case 2';
}

enum IntEnum: int
{
	case One = 1;
	case Two = 2;
}

enum StringIntEnum: string
{
	case One = '1';
	case Two = '2';
}

class FormWithEnum
{
	public function __construct(
		public TestEnum $enum1,
		public ?TestEnum $enum2,
	) {
	}
}

class FormWithIntEnum
{
	public function __construct(
		public IntEnum $value,
	) {
	}
}

class FormWithStringIntEnum
{
	public function __construct(
		public StringIntEnum $value,
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


test('mapping a numeric string to an int-backed enum', function () {
	$form = new Form;
	$form->addText('value');
	$form->setValues(['value' => '1']);

	Assert::equal(new FormWithIntEnum(IntEnum::One), $form->getValues(FormWithIntEnum::class));
});


test('mapping a numeric string to a string-backed enum', function () {
	$form = new Form;
	$form->addText('value');
	$form->setValues(['value' => '1']);

	Assert::equal(new FormWithStringIntEnum(StringIntEnum::One), $form->getValues(FormWithStringIntEnum::class));
});


test('incompatible values are left unchanged instead of raising TypeError/ValueError', function () {
	$intRef = new ReflectionProperty(FormWithIntEnum::class, 'value');
	$strRef = (new ReflectionClass(FormWithEnum::class))->getConstructor()->getParameters()[0];
	$strIntRef = new ReflectionProperty(FormWithStringIntEnum::class, 'value');

	// int-backed enum
	Assert::same(IntEnum::One, Helpers::tryEnumConversion('1', $intRef));
	Assert::same('abc', Helpers::tryEnumConversion('abc', $intRef)); // was TypeError
	Assert::same('', Helpers::tryEnumConversion('', $intRef)); // empty field, was TypeError
	Assert::same('9', Helpers::tryEnumConversion('9', $intRef)); // valid type, unknown case, was ValueError

	// string-backed enum
	Assert::same(TestEnum::Case1, Helpers::tryEnumConversion('case 1', $strRef));
	Assert::same('zzz', Helpers::tryEnumConversion('zzz', $strRef)); // unknown case, was ValueError

	// string-backed enum with numeric values (submitted as string)
	Assert::same(StringIntEnum::One, Helpers::tryEnumConversion('1', $strIntRef));
	Assert::same('9', Helpers::tryEnumConversion('9', $strIntRef)); // unknown case, was ValueError

	Assert::null(Helpers::tryEnumConversion(null, $intRef));
});
