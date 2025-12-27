<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = [
		'title' => 'sent title',
		'first' => [
			'age' => '999',
			'second' => [
				'city' => 'sent city',
			],
		],
	];
	Form::initialize(true);
});


function createForm(): Form
{
	$form = new Form;
	$form->allowCrossOrigin();
	$form->addText('title');

	$first = $form->addContainer('first');
	$first->addText('name');
	$first->addInteger('age');

	$second = $first->addContainer('second');
	$second->addText('city');
	return $form;
}


test('setting defaults using ArrayHash', function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';

	$form = createForm();
	Assert::false($form->isSubmitted());

	$form->setDefaults(ArrayHash::from([
		'title' => 'xxx',
		'extra' => '50',
		'first' => ArrayHash::from([
			'name' => 'yyy',
			'age' => '30',
			'second' => ArrayHash::from([
				'city' => 'zzz',
			]),
		]),
	]));

	Assert::equal(ArrayHash::from([
		'title' => 'xxx',
		'first' => ArrayHash::from([
			'name' => 'yyy',
			'age' => '30',
			'second' => ArrayHash::from([
				'city' => 'zzz',
			]),
		]),
	]), $form->getValues());
});


test('retrieving POST data as ArrayHash', function () {
	$form = createForm();
	Assert::truthy($form->isSubmitted());
	Assert::equal(ArrayHash::from([
		'title' => 'sent title',
		'first' => ArrayHash::from([
			'name' => '',
			'age' => 999,
			'second' => ArrayHash::from([
				'city' => 'sent city',
			]),
		]),
	]), $form->getValues());
});


test('resetting form with ArrayHash values', function () {
	$form = createForm();
	Assert::truthy($form->isSubmitted());

	$form->reset();

	Assert::false($form->isSubmitted());
	Assert::equal(ArrayHash::from([
		'title' => '',
		'first' => ArrayHash::from([
			'name' => '',
			'age' => null,
			'second' => ArrayHash::from([
				'city' => '',
			]),
		]),
	]), $form->getValues());
});


test('updating values with ArrayHash and erase', function () {
	$form = createForm();
	Assert::truthy($form->isSubmitted());

	$form->setValues(ArrayHash::from([
		'title' => 'new1',
		'first' => ArrayHash::from([
			'name' => 'new2',
		]),
	]));

	Assert::equal(ArrayHash::from([
		'title' => 'new1',
		'first' => ArrayHash::from([
			'name' => 'new2',
			'age' => 999,
			'second' => ArrayHash::from([
				'city' => 'sent city',
			]),
		]),
	]), $form->getValues());

	// erase
	$form->setValues(ArrayHash::from([
		'title' => 'new1',
		'first' => ArrayHash::from([
			'name' => 'new2',
		]),
	]), true);

	Assert::equal(ArrayHash::from([
		'title' => 'new1',
		'first' => ArrayHash::from([
			'name' => 'new2',
			'age' => null,
			'second' => ArrayHash::from([
				'city' => '',
			]),
		]),
	]), $form->getValues());
});


test('onSuccess event with ArrayHash values', function () {
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
