<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Form::Enum validation rule.
 */

use Nette\Forms\Form;
use Nette\Forms\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


enum StringEnum: string
{
	case Active = 'active';
	case Inactive = 'inactive';
}

enum IntEnum: int
{
	case One = 1;
	case Two = 2;
}

enum PureEnum
{
	case X;
	case Y;
}


test('string-backed enum accepts a valid case and rejects others', function () {
	$form = new Form;
	$input = $form->addText('status');
	$input->addRule(Form::Enum, null, StringEnum::class);

	$input->setValue('active');
	Assert::true($input->getRules()->validate());

	$input->setValue('bogus');
	Assert::false($input->getRules()->validate());
	Assert::same(['Please select a valid option.'], $input->getErrors());
});


test('int-backed enum coerces submitted strings and rejects non-members', function () {
	$form = new Form;
	$input = $form->addText('value');
	$input->addRule(Form::Enum, null, IntEnum::class);

	$input->setValue('1'); // HTTP values arrive as strings
	Assert::true($input->getRules()->validate());

	$input->setValue('9'); // valid type, unknown case
	Assert::false($input->getRules()->validate());

	$input->setValue('abc'); // non-numeric, was TypeError before
	Assert::false($input->getRules()->validate());
});


test('empty optional field skips the enum rule', function () {
	$form = new Form;
	$input = $form->addText('status');
	$input->setRequired(false);
	$input->addRule(Form::Enum, null, StringEnum::class);

	$input->setValue('');
	Assert::true($input->getRules()->validate());
});


test('multi-value control validates every item', function () {
	$form = new Form;
	$input = $form->addMultiSelect('tags', null, ['active' => 'A', 'inactive' => 'B', 'x' => 'X']);
	$input->addRule(Form::Enum, null, StringEnum::class);

	$input->setValue(['active', 'inactive']);
	Assert::true($input->getRules()->validate());

	$input->setValue(['active', 'x']);
	Assert::false($input->getRules()->validate());
});


test('the rule exports to JS as a membership check against case values', function () {
	$form = new Form;
	$input = $form->addText('status');
	$input->addRule(Form::Enum, 'Choose wisely', StringEnum::class);

	Assert::same([
		['op' => ':equal', 'msg' => 'Choose wisely', 'arg' => ['active', 'inactive']],
	], Helpers::exportRules($input->getRules()));

	$form2 = new Form;
	$input2 = $form2->addText('value');
	$input2->addRule(Form::Enum, 'Choose wisely', IntEnum::class);

	Assert::same([
		['op' => ':equal', 'msg' => 'Choose wisely', 'arg' => [1, 2]],
	], Helpers::exportRules($input2->getRules()));
});


test('a non-backed enum yields a clear exception instead of a cryptic fatal', function () {
	$form = new Form;
	$input = $form->addText('status');
	$input->addRule(Form::Enum, null, PureEnum::class);
	$input->setValue('X');

	Assert::exception(
		fn() => $input->getRules()->validate(),
		Nette\InvalidArgumentException::class,
		"The Enum validator requires a backed enum class, '%a%' given.",
	);
});
