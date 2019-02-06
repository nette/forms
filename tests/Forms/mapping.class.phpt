<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class FormData
{
	/** @var string */
	public $name;

	/** @var FormFirstData */
	public $first;
}


class FormFirstData
{
	/** @var string */
	public $name;

	/** @var int */
	public $age;

	/** @var FormSecondData */
	public $second;
}


class FormSecondData
{
	/** @var string */
	public $name;
}


function hydrate(string $class, array $data)
{
	$obj = new $class;
	foreach ($data as $key => $value) {
		$obj->$key = $value;
	}
	return $obj;
}


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

	$form->setDefaults(hydrate(FormData::class, [
		'name' => 'xxx',
		'extra' => '50',
		'first' => hydrate(FormFirstData::class, [
			'name' => 'yyy',
			'age' => '30',
			'second' => hydrate(FormSecondData::class, [
				'name' => 'zzz',
			]),
		]),
	]));

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
	Assert::equal(ArrayHash::from([
		'name' => 'jim',
		'first' => ArrayHash::from([
			'name' => '',
			'age' => '40',
			'second' => ArrayHash::from([
				'name' => 'jack',
			]),
		]),
	]), $form->getValues());
});


test(function () { // setValues() test
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	Assert::truthy($form->isSubmitted());

	$form->setValues(hydrate(FormData::class, [
		'name' => 'new1',
		'first' => hydrate(FormFirstData::class, [
			'name' => 'new2',
		]),
	]));

	Assert::equal(ArrayHash::from([
		'name' => 'new1',
		'first' => ArrayHash::from([
			'name' => 'new2',
			'age' => '',
			'second' => ArrayHash::from([
				'name' => 'jack',
			]),
		]),
	]), $form->getValues());


	// erase
	$form->setValues(hydrate(FormData::class, [
		'name' => 'new1',
		'first' => hydrate(FormFirstData::class, [
			'name' => 'new2',
		]),
	]), true);

	Assert::equal(ArrayHash::from([
		'name' => 'new1',
		'first' => ArrayHash::from([
			'name' => 'new2',
			'age' => '',
			'second' => ArrayHash::from([
				'name' => '',
			]),
		]),
	]), $form->getValues());
});


test(function () { // reset() test
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	Assert::truthy($form->isSubmitted());

	$form->reset();

	Assert::false($form->isSubmitted());
	Assert::equal(ArrayHash::from([
		'name' => '',
		'first' => ArrayHash::from([
			'name' => '',
			'age' => '',
			'second' => ArrayHash::from([
				'name' => '',
			]),
		]),
	]), $form->getValues());
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
