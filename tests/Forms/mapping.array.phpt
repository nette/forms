<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_POST = [
	'name' => 'jim',
	'first' => [
		'age' => '40',
		'second' => [
			'name' => 'jack',
		],
	],
];


function createForm(): Form
{
	$form = new Form;
	$form->addText('name');

	$first = $form->addContainer('first');
	$first->addText('name');
	$first->addText('age');

	$second = $first->addContainer('second');
	$second->addText('name');
	return $form;
}


test(function () { // setDefaults() test
	$form = createForm();
	Assert::false($form->isSubmitted());

	$form->setDefaults([
		'name' => 'xxx',
		'extra' => '50',
		'first' => [
			'name' => 'yyy',
			'age' => '30',
			'second' => [
				'name' => 'zzz',
			],
		],
	]);

	Assert::same([
		'name' => 'xxx',
		'first' => [
			'name' => 'yyy',
			'age' => '30',
			'second' => [
				'name' => 'zzz',
			],
		],
	], $form->getValues(true));
});


test(function () { // submitted form
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	Assert::truthy($form->isSubmitted());
	Assert::equal([
		'name' => 'jim',
		'first' => [
			'name' => '',
			'age' => '40',
			'second' => [
				'name' => 'jack',
			],
		],
	], $form->getValues(true));
});


test(function () { // setValues() test
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	Assert::truthy($form->isSubmitted());

	$form->setValues([
		'name' => 'new1',
		'first' => [
			'name' => 'new2',
		],
	]);

	Assert::equal([
		'name' => 'new1',
		'first' => [
			'name' => 'new2',
			'age' => '40',
			'second' => [
				'name' => 'jack',
			],
		],
	], $form->getValues(true));

	// erase
	$form->setValues([
		'name' => 'new1',
		'first' => [
			'name' => 'new2',
		],
	], true);

	Assert::equal([
		'name' => 'new1',
		'first' => [
			'name' => 'new2',
			'age' => '',
			'second' => [
				'name' => '',
			],
		],
	], $form->getValues(true));
});


test(function () { // reset() test
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	Assert::truthy($form->isSubmitted());

	$form->reset();

	Assert::false($form->isSubmitted());
	Assert::equal([
		'name' => '',
		'first' => [
			'name' => '',
			'age' => '',
			'second' => [
				'name' => '',
			],
		],
	], $form->getValues(true));
});


test(function () { // onSuccess test
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	$form->onSuccess[] = function (Form $form, array $values) {
		Assert::same([
			'name' => 'jim',
			'first' => [
				'name' => '',
				'age' => '40',
				'second' => [
					'name' => 'jack',
				],
			],
		], $values);
	};

	$form->onSuccess[] = function (Form $form, ArrayHash $values) {
		Assert::equal(ArrayHash::from([
			'name' => 'jim',
			'first' => ArrayHash::from([
				'name' => '',
				'age' => '40',
				'second' => ArrayHash::from([
					'name' => 'jack',
				]),
			]),
		]), $values);
	};

	$form->onSuccess[] = function (Form $form, $values) {
		Assert::equal(ArrayHash::from([
			'name' => 'jim',
			'first' => ArrayHash::from([
				'name' => '',
				'age' => '40',
				'second' => ArrayHash::from([
					'name' => 'jack',
				]),
			]),
		]), $values);
	};

	Assert::truthy($form->isSubmitted());
	$form->fireEvents();
});
