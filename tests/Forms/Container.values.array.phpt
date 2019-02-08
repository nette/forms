<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_POST = [
	'title' => 'sent title',
	'first' => [
		'age' => '999',
		'second' => [
			'city' => 'sent city',
		],
	],
];


function createForm(): Form
{
	$form = new Form;
	$form->addText('title');

	$first = $form->addContainer('first');
	$first->addText('name');
	$first->addInteger('age');

	$second = $first->addContainer('second');
	$second->addText('city');
	return $form;
}


test(function () { // setDefaults() + array
	$form = createForm();
	Assert::false($form->isSubmitted());

	$form->setDefaults([
		'title' => 'xxx',
		'extra' => '50',
		'first' => [
			'name' => 'yyy',
			'age' => 30,
			'second' => [
				'city' => 'zzz',
			],
		],
	]);

	Assert::same([
		'title' => 'xxx',
		'first' => [
			'name' => 'yyy',
			'age' => 30,
			'second' => [
				'city' => 'zzz',
			],
		],
	], $form->getValues(true));
});


test(function () { // submitted form + getValues(true)
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	Assert::truthy($form->isSubmitted());
	Assert::equal([
		'title' => 'sent title',
		'first' => [
			'name' => '',
			'age' => '999',
			'second' => [
				'city' => 'sent city',
			],
		],
	], $form->getValues(true));
});


test(function () { // submitted form + reset()
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	Assert::truthy($form->isSubmitted());

	$form->reset();

	Assert::false($form->isSubmitted());
	Assert::equal([
		'title' => '',
		'first' => [
			'name' => '',
			'age' => null,
			'second' => [
				'city' => '',
			],
		],
	], $form->getValues(true));
});


test(function () { // setValues() + array
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	Assert::truthy($form->isSubmitted());

	$form->setValues([
		'title' => 'new1',
		'first' => [
			'name' => 'new2',
		],
	]);

	Assert::equal([
		'title' => 'new1',
		'first' => [
			'name' => 'new2',
			'age' => '999',
			'second' => [
				'city' => 'sent city',
			],
		],
	], $form->getValues(true));

	// erase
	$form->setValues([
		'title' => 'new1',
		'first' => [
			'name' => 'new2',
		],
	], true);

	Assert::equal([
		'title' => 'new1',
		'first' => [
			'name' => 'new2',
			'age' => null,
			'second' => [
				'city' => '',
			],
		],
	], $form->getValues(true));
});


test(function () { // onSuccess test
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	$form->onSuccess[] = function (Form $form, array $values) {
		Assert::same([
			'title' => 'sent title',
			'first' => [
				'name' => '',
				'age' => 999,
				'second' => [
					'city' => 'sent city',
				],
			],
		], $values);
	};

	$form->onSuccess[] = function (Form $form, ArrayHash $values) {
		Assert::equal(ArrayHash::from([
			'title' => 'sent title',
			'first' => ArrayHash::from([
				'name' => '',
				'age' => 999,
				'second' => ArrayHash::from([
					'city' => 'sent city',
				]),
			]),
		]), $values);
	};

	$form->onSuccess[] = function (Form $form, $values) {
		Assert::equal(ArrayHash::from([
			'title' => 'sent title',
			'first' => ArrayHash::from([
				'name' => '',
				'age' => 999,
				'second' => ArrayHash::from([
					'city' => 'sent city',
				]),
			]),
		]), $values);
	};

	$ok = false;
	$form->onSuccess[] = function () use (&$ok) {
		$ok = true;
	};

	$form->fireEvents();
	Assert::true($ok);
});
