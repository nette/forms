<?php

/**
 * @phpVersion 8.0
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class FormDataConstruct
{
	public function __construct(
		public string $title,
		public FormFirstLevelConstruct $first,
		...$extra,
	) {
	}
}


class FormFirstLevelConstruct
{
	public function __construct(
		public string $name,
		public ?FormSecondLevelConstruct $second = null,
		public int|null $age = null,
	) {
	}
}


class FormSecondLevelConstruct
{
	public function __construct(
		public string $city,
	) {
	}
}


function hydrate(string $class, array $data)
{
	return new $class(...$data);
}


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


test('getValues(...arguments...)', function () {
	$form = createForm();

	$form->setValues([
		'title' => 'new1',
		'first' => [
			'name' => 'new2',
		],
	]);

	Assert::equal(new FormDataConstruct(
		title: 'new1',
		first: new FormFirstLevelConstruct(
			name: 'new2',
			age: null,
			second: new FormSecondLevelConstruct(
				city: '',
			),
		),
	), $form->getValues(FormDataConstruct::class));

	$form->setMappedType(FormDataConstruct::class);
	$form['first']->setMappedType(FormFirstLevelConstruct::class);
	$form['first-second']->setMappedType(FormSecondLevelConstruct::class);

	Assert::equal(new FormDataConstruct(
		title: 'new1',
		first: new FormFirstLevelConstruct(
			name: 'new2',
			age: null,
			second: new FormSecondLevelConstruct(
				city: '',
			),
		),
	), $form->getValues());
});
