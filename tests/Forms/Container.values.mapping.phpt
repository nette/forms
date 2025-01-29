<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


#[AllowDynamicProperties]
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


setUp(function () {
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
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
});


function createForm(): Form
{
	ob_start();
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


test('setting defaults using object mapping', function () {
	$_SERVER['REQUEST_METHOD'] = null;

	$form = createForm();
	Assert::false($form->isSubmitted());

	$form->setDefaults(Arrays::toObject([
		'title' => 'xxx',
		'extra' => '50',
		'first' => Arrays::toObject([
			'name' => 'yyy',
			'age' => 30,
			'second' => Arrays::toObject([
				'city' => 'zzz',
			], new FormSecondLevel),
		], new FormFirstLevel),
	], new FormData));

	Assert::same([
		'title' => 'xxx',
		'first' => [
			'name' => 'yyy',
			'age' => 30,
			'second' => [
				'city' => 'zzz',
			],
		],
	], $form->getValues('array'));
});


test('mapping POST data to objects', function () {
	$form = createForm();
	$form->setMappedType(FormData::class);

	Assert::truthy($form->isSubmitted());
	Assert::equal(Arrays::toObject([
		'title' => 'sent title',
		'first' => Arrays::toObject([
			'name' => '',
			'age' => 999,
			'second' => Arrays::toObject([
				'city' => 'sent city',
			], new FormSecondLevel),
		], new FormFirstLevel),
	], new FormData), $form->getValues());
});


test('resetting form with object-mapped values', function () {
	$form = createForm();
	$form->setMappedType(FormData::class);

	Assert::truthy($form->isSubmitted());

	$form->reset();

	Assert::false($form->isSubmitted());
	Assert::equal(Arrays::toObject([
		'title' => '',
		'first' => Arrays::toObject([
			'name' => '',
			'age' => null,
			'second' => Arrays::toObject([
				'city' => '',
			], new FormSecondLevel),
		], new FormFirstLevel),
	], new FormData), $form->getValues());
});


test('updating object-mapped values with erase', function () {
	$form = createForm();
	$form->setMappedType(FormData::class);

	Assert::truthy($form->isSubmitted());

	$form->setValues(Arrays::toObject([
		'title' => 'new1',
		'first' => Arrays::toObject([
			'name' => 'new2',
			// age => null
		], new FormFirstLevel),
	], new FormData));

	Assert::equal(Arrays::toObject([
		'title' => 'new1',
		'first' => Arrays::toObject([
			'name' => 'new2',
			'age' => null,
			'second' => Arrays::toObject([
				'city' => 'sent city',
			], new FormSecondLevel),
		], new FormFirstLevel),
	], new FormData), $form->getValues());

	// erase
	$form->setValues(Arrays::toObject([
		'title' => 'new1',
		'first' => Arrays::toObject([
			'name' => 'new2',
		], new FormFirstLevel),
	], new FormData), true);

	Assert::equal(Arrays::toObject([
		'title' => 'new1',
		'first' => Arrays::toObject([
			'name' => 'new2',
			'age' => null,
			'second' => Arrays::toObject([
				'city' => '',
			], new FormSecondLevel),
		], new FormFirstLevel),
	], new FormData), $form->getValues());
});


test('mixed object and array value mapping', function () {
	$_SERVER['REQUEST_METHOD'] = null;

	$form = createForm();

	$form->setValues([
		'title' => 'new1',
		'first' => [
			'name' => 'new2',
		],
	]);

	Assert::equal(Arrays::toObject([
		'title' => 'new1',
		'first' => Arrays::toObject([
			'name' => 'new2',
			'age' => null,
			'second' => Arrays::toObject([
				'city' => '',
			], new FormSecondLevel),
		], new FormFirstLevel),
	], new FormData), $form->getValues(FormData::class));

	$form->setMappedType(FormData::class);
	$form['first']->setMappedType(FormFirstLevel::class);
	$form['first-second']->setMappedType(FormSecondLevel::class);

	Assert::equal(Arrays::toObject([
		'title' => 'new1',
		'first' => Arrays::toObject([
			'name' => 'new2',
			'age' => null,
			'second' => Arrays::toObject([
				'city' => '',
			], new FormSecondLevel),
		], new FormFirstLevel),
	], new FormData), $form->getValues());

	Assert::equal([
		'title' => 'new1',
		'first' => Arrays::toObject([
			'name' => 'new2',
			'age' => null,
			'second' => Arrays::toObject([
				'city' => '',
			], new FormSecondLevel),
		], new FormFirstLevel),
	], $form->getValues('array'));
});


test('onSuccess with multiple mapped value types', function () {
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
		Assert::equal(Arrays::toObject([
			'title' => 'sent title',
			'first' => Arrays::toObject([
				'name' => '',
				'age' => 999,
				'second' => Arrays::toObject([
					'city' => 'sent city',
				], new FormSecondLevel),
			], new FormFirstLevel),
		], new FormData), $values);
	};

	$form->onSuccess[] = function (Form $form, FormData $values) {
		Assert::equal(Arrays::toObject([
			'title' => 'sent title',
			'first' => Arrays::toObject([
				'name' => '',
				'age' => 999,
				'second' => Arrays::toObject([
					'city' => 'sent city',
				], new FormSecondLevel),
			], new FormFirstLevel),
		], new FormData), $values);
	};

	$ok = false;
	$form->onSuccess[] = function () use (&$ok) {
		$ok = true;
	};

	$form->fireEvents();
	Assert::true($ok);
});


test('populating existing object with form values', function () {
	$form = createForm();
	$obj = $orig = new FormData;

	Assert::equal(Arrays::toObject([
		'title' => 'sent title',
		'first' => Arrays::toObject([
			'name' => '',
			'age' => 999,
			'second' => Arrays::toObject([
				'city' => 'sent city',
			], new FormSecondLevel),
		], new FormFirstLevel),
	], new FormData), $form->getValues($obj));

	Assert::same($obj, $orig);
});


test('validation scope on object-mapped fields', function () {
	$_POST['send'] = '';

	$form = createForm();
	$form->addSubmit('send')->setValidationScope([$form['title'], $form['first']['age']]);

	Assert::truthy($form->isSubmitted());
	Assert::equal(Arrays::toObject([
		'title' => 'sent title',
		'first' => Arrays::toObject([
			'age' => 999,
			'second' => new FormSecondLevel,
		], new FormFirstLevel),
	], new FormData), $form->getValues(FormData::class));
});


test('validation scope on nested object fields', function () {
	$_POST['send'] = '';

	$form = createForm();
	$form->addSubmit('send')->setValidationScope([$form['title'], $form['first']['second']]);

	Assert::truthy($form->isSubmitted());
	Assert::equal(Arrays::toObject([
		'title' => 'sent title',
		'first' => Arrays::toObject([
			'second' => Arrays::toObject([
				'city' => 'sent city',
			], new FormSecondLevel),
		], new FormFirstLevel),
	], new FormData), $form->getValues(FormData::class));
});
