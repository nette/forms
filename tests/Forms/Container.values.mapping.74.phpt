<?php

/**
 * @phpVersion 7.4
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class FormData
{
	public string $title;

	public FormFirstLevel $first;
}


class FormFirstLevel
{
	public string $name;

	public ?int $age = null;

	public ?FormSecondLevel $second;
}


class FormSecondLevel
{
	public string $city;
}


function hydrate(string $class, array $data)
{
	$obj = new $class;
	foreach ($data as $key => $value) {
		$obj->$key = $value;
	}
	return $obj;
}


$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';
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
	Form::initialize(true);

	$form = new Form;
	$form->addText('title');

	$first = $form->addContainer('first');
	$first->addText('name');
	$first->addInteger('age');

	$second = $first->addContainer('second');
	$second->addText('city');
	return $form;
}


test('setDefaults() + object', function () {
	$form = createForm();
	Assert::false($form->isSubmitted());

	$form->setDefaults(hydrate(FormData::class, [
		'title' => 'xxx',
		'extra' => '50',
		'first' => hydrate(FormFirstLevel::class, [
			'name' => 'yyy',
			'age' => 30,
			'second' => hydrate(FormSecondLevel::class, [
				'city' => 'zzz',
			]),
		]),
	]));

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


test('submitted form + getValues()', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	$form->setMappedType(FormData::class);

	Assert::truthy($form->isSubmitted());
	Assert::equal(hydrate(FormData::class, [
		'title' => 'sent title',
		'first' => hydrate(FormFirstLevel::class, [
			'name' => '',
			'age' => 999,
			'second' => hydrate(FormSecondLevel::class, [
				'city' => 'sent city',
			]),
		]),
	]), $form->getValues());
});


test('submitted form + reset()', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	$form->setMappedType(FormData::class);

	Assert::truthy($form->isSubmitted());

	$form->reset();

	Assert::false($form->isSubmitted());
	Assert::equal(hydrate(FormData::class, [
		'title' => '',
		'first' => hydrate(FormFirstLevel::class, [
			'name' => '',
			'age' => null,
			'second' => hydrate(FormSecondLevel::class, [
				'city' => '',
			]),
		]),
	]), $form->getValues());
});


test('setValues() + object', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	$form->setMappedType(FormData::class);

	Assert::truthy($form->isSubmitted());

	$form->setValues(hydrate(FormData::class, [
		'title' => 'new1',
		'first' => hydrate(FormFirstLevel::class, [
			'name' => 'new2',
			// age => null
		]),
	]));

	Assert::equal(hydrate(FormData::class, [
		'title' => 'new1',
		'first' => hydrate(FormFirstLevel::class, [
			'name' => 'new2',
			'age' => null,
			'second' => hydrate(FormSecondLevel::class, [
				'city' => 'sent city',
			]),
		]),
	]), $form->getValues());

	// erase
	$form->setValues(hydrate(FormData::class, [
		'title' => 'new1',
		'first' => hydrate(FormFirstLevel::class, [
			'name' => 'new2',
		]),
	]), true);

	Assert::equal(hydrate(FormData::class, [
		'title' => 'new1',
		'first' => hydrate(FormFirstLevel::class, [
			'name' => 'new2',
			'age' => null,
			'second' => hydrate(FormSecondLevel::class, [
				'city' => '',
			]),
		]),
	]), $form->getValues());
});


test('getValues(...arguments...)', function () {
	$_SERVER['REQUEST_METHOD'] = null;

	$form = createForm();

	$form->setValues([
		'title' => 'new1',
		'first' => [
			'name' => 'new2',
		],
	]);

	Assert::equal(hydrate(FormData::class, [
		'title' => 'new1',
		'first' => hydrate(FormFirstLevel::class, [
			'name' => 'new2',
			'age' => null,
			'second' => hydrate(FormSecondLevel::class, [
				'city' => '',
			]),
		]),
	]), $form->getValues(FormData::class));

	$form->setMappedType(FormData::class);
	$form['first']->setMappedType(FormFirstLevel::class);
	$form['first-second']->setMappedType(FormSecondLevel::class);

	Assert::equal(hydrate(FormData::class, [
		'title' => 'new1',
		'first' => hydrate(FormFirstLevel::class, [
			'name' => 'new2',
			'age' => null,
			'second' => hydrate(FormSecondLevel::class, [
				'city' => '',
			]),
		]),
	]), $form->getValues());

	Assert::equal([
		'title' => 'new1',
		'first' => hydrate(FormFirstLevel::class, [
			'name' => 'new2',
			'age' => null,
			'second' => hydrate(FormSecondLevel::class, [
				'city' => '',
			]),
		]),
	], $form->getValues(true));
});


test('onSuccess test', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';

	$form = createForm();
	$form->setMappedType(FormData::class);

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
		Assert::equal(hydrate(FormData::class, [
			'title' => 'sent title',
			'first' => hydrate(FormFirstLevel::class, [
				'name' => '',
				'age' => 999,
				'second' => hydrate(FormSecondLevel::class, [
					'city' => 'sent city',
				]),
			]),
		]), $values);
	};

	$form->onSuccess[] = function (Form $form, FormData $values) {
		Assert::equal(hydrate(FormData::class, [
			'title' => 'sent title',
			'first' => hydrate(FormFirstLevel::class, [
				'name' => '',
				'age' => 999,
				'second' => hydrate(FormSecondLevel::class, [
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
