<?php

/**
 * Test: Nette\Forms\Controls\BaseControl
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Validator;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	Form::initialize(true);
});


test('error handling', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setRequired('error');

	Assert::same([], $input->getErrors());
	Assert::null($input->getError());
	Assert::false($input->hasErrors());

	$input->validate();

	Assert::same(['error'], $input->getErrors());
	Assert::same('error', $input->getError());
	Assert::true($input->hasErrors());

	$input->cleanErrors();
	Assert::false($input->hasErrors());
});


test('validators', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->setValue(123);

	Assert::true(Validator::validateEqual($input, 123));
	Assert::true(Validator::validateEqual($input, '123'));
	Assert::true(Validator::validateEqual($input, [123, 3])); // "is in"
	Assert::false(Validator::validateEqual($input, ['x']));
	Assert::false(Validator::validateEqual($input, []));

	Assert::true(Validator::validateFilled($input));
	Assert::true(Validator::validateValid($input));

	Assert::false(Validator::validateLength($input, null));
	Assert::false(Validator::validateLength($input, 2));
	Assert::true(Validator::validateLength($input, 3));

	Assert::true(Validator::validateMinLength($input, 3));
	Assert::false(Validator::validateMinLength($input, 4));

	Assert::true(Validator::validateMaxLength($input, 3));
	Assert::false(Validator::validateMaxLength($input, 2));

	Assert::false(Validator::validateRange($input, [null, null]));
	Assert::true(Validator::validateRange($input, [100, 1000]));
	Assert::false(Validator::validateRange($input, [1000, null]));

	Assert::true(Validator::validateMin($input, 122));
	Assert::true(Validator::validateMin($input, 123));
	Assert::false(Validator::validateMin($input, 124));

	Assert::false(Validator::validateMax($input, 122));
	Assert::true(Validator::validateMax($input, 123));
	Assert::true(Validator::validateMax($input, 124));
});


test('validators for array', function () {
	$form = new Form;
	$input = $form->addMultiSelect('select', null, ['a', 'b', 'c', 'd']);
	$input->setValue([1, 2, 3]);

	Assert::true(Validator::validateEqual($input, [1, 2, 3, 4]));
	Assert::true(Validator::validateEqual($input, ['1', '2', '3']));
	Assert::false(Validator::validateEqual($input, ['x']));

	Assert::true(Validator::validateFilled($input));
	Assert::true(Validator::validateValid($input));

	Assert::false(Validator::validateLength($input, null));
	Assert::false(Validator::validateLength($input, 2));
	Assert::true(Validator::validateLength($input, 3));

	Assert::true(Validator::validateMinLength($input, 3));
	Assert::false(Validator::validateMinLength($input, 4));

	Assert::true(Validator::validateMaxLength($input, 3));
	Assert::false(Validator::validateMaxLength($input, 2));
});


test('setHtmlId', function () {
	$form = new Form;
	$input = $form->addText('text')->setHtmlId('myId');

	Assert::same('<input type="text" name="text" id="myId">', (string) $input->getControl());
});


test('special name', function () {
	$form = new Form;
	$input = $form->addText('submit');

	Assert::same('<input type="text" name="_submit" id="frm-submit">', (string) $input->getControl());
});


test('disabled', function () {
	$form = new Form;
	$form->addText('disabled')
		->setDisabled()
		->setDefaultValue('default');

	Assert::false($form->isSubmitted());
	Assert::true($form['disabled']->isDisabled());
	Assert::same('default', $form['disabled']->getValue());
});


test('disabled & submitted', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = ['disabled' => 'submitted value'];
	$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';

	$form = new Form;
	$form->addText('disabled')
		->setDisabled()
		->setDefaultValue('default');

	Assert::true($form->isSubmitted());
	Assert::same('default', $form['disabled']->getValue());

	unset($form['disabled']);
	$input = new Nette\Forms\Controls\TextInput;
	$input->setDisabled()
		->setDefaultValue('default');

	$form['disabled'] = $input;

	Assert::same('default', $input->getValue());
});


test('', function () {
	$form = new Form;
	$form->setTranslator(new class implements Nette\Localization\ITranslator {
		public function translate($s, ...$parameters): string
		{
			return strtolower($s);
		}
	});

	Validator::$messages[Form::Filled] = '"%label" field is required.';

	$input = $form->addSelect('list1', 'LIST', [
		'a' => 'First',
		0 => 'Second',
	])->setRequired();

	$input->validate();

	Assert::match('<label for="frm-list1">list</label>', (string) $input->getLabel());
	Assert::same(['"list" field is required.'], $input->getErrors());

	$input = $form->addSelect('list2', 'LIST', [
		'a' => 'First',
		0 => 'Second',
	])->setTranslator(null)
		->setRequired();

	$input->validate();

	Assert::match('<label for="frm-list2">list</label>', (string) $input->getLabel());
	Assert::same(['"list" field is required.'], $input->getErrors());
});


test('change HTML name', function () {
	$_POST = ['b' => '123', 'send' => ''];
	$form = new Form;
	$form->addSubmit('send', 'Send');
	$input = $form->addText('a');

	Assert::same('', $input->getValue());
	$input->setHtmlAttribute('name', 'b');
	Assert::same('123', $input->getValue());

	Assert::match('<input type="text" name="b" id="frm-a" value="123">', (string) $input->getControl());
});
