<?php declare(strict_types=1);

/**
 * Test: Nette\Forms placeholder %value with non-scalar values.
 */

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('array value', function () {
	$form = new Form;
	$form->addCheckboxList('list', null, ['a' => 'A', 'b' => 'B'])
		->setDefaultValue(['a', 'b'])
		->addRule(Form::MaxLength, 'Too many: %value', 1);

	$form->validate();
	Assert::same(['Too many: a, b'], $form->getErrors());
});


test('date value', function () {
	$form = new Form;
	$form->addDate('date')
		->setDefaultValue('2023-10-22')
		->addRule(Form::Max, '%value is too late', new DateTime('2020-01-01'));

	$form->validate();
	Assert::count(1, $form->getErrors());
	Assert::match('%a%2023 is too late', $form->getErrors()[0]);
});


test('uploaded file shows its name, not the temporary path', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_FILES = [
		'single' => [
			'name' => 'doc.html',
			'type' => 'text/html',
			'tmp_name' => __DIR__ . '/../Controls/files/logo.gif',
			'error' => 0,
			'size' => 100,
		],
		'multi' => [
			'name' => ['a.html', 'b.html'],
			'type' => ['text/html', 'text/html'],
			'tmp_name' => [__DIR__ . '/../Controls/files/logo.gif', __DIR__ . '/../Controls/files/logo.gif'],
			'error' => [0, 0],
			'size' => [100, 100],
		],
	];

	$form = new Form;
	$form->allowCrossOrigin();
	$form->addUpload('single')
		->addRule(Form::MimeType, 'Unsupported %value.', 'text/plain');
	$form->addMultiUpload('multi')
		->addRule(Form::MimeType, 'Unsupported %value.', 'text/plain');

	$form->validate();
	Assert::same(['Unsupported doc.html.', 'Unsupported a.html, b.html.'], $form->getErrors());
});
