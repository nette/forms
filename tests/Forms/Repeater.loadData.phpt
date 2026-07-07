<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Repeater.
 */

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = $_FILES = [];
	Form::initialize(true);
});


test('empty repeater submission', function () {
	$_POST = [];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addText('email');
	});

	Assert::true($form->isValid());
	Assert::same([], $repeater->getValues('array'));
	Assert::count(0, $repeater->getComponents());
});


test('single item submission', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'John', 'email' => 'john@example.com'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addText('email');
	});

	Assert::true($form->isValid());
	Assert::count(1, $repeater->getComponents());
	Assert::same([
		['name' => 'John', 'email' => 'john@example.com'],
	], $repeater->getValues('array'));
});


test('multiple items submission', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'John', 'email' => 'john@example.com'],
		1 => ['name' => 'Jane', 'email' => 'jane@example.com'],
		2 => ['name' => 'Bob', 'email' => 'bob@example.com'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addText('email');
	});

	Assert::true($form->isValid());
	Assert::count(3, $repeater->getComponents());
	Assert::same([
		['name' => 'John', 'email' => 'john@example.com'],
		['name' => 'Jane', 'email' => 'jane@example.com'],
		['name' => 'Bob', 'email' => 'bob@example.com'],
	], $repeater->getValues('array'));
});


test('non-sequential indexes are renumbered', function () {
	$_POST = ['persons' => [
		5 => ['name' => 'John'],
		0 => ['name' => 'Jane'],
		10 => ['name' => 'Bob'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	Assert::true($form->isValid());
	Assert::count(3, $repeater->getComponents());

	// Check that components are renumbered to 0, 1, 2
	Assert::same('John', $repeater->getComponent('0')['name']->getValue());
	Assert::same('Jane', $repeater->getComponent('1')['name']->getValue());
	Assert::same('Bob', $repeater->getComponent('2')['name']->getValue());
});


test('partial empty fields submission', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'John', 'email' => ''],
		1 => ['name' => '', 'email' => 'jane@example.com'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addText('email');
	});

	Assert::true($form->isValid());
	Assert::same([
		['name' => 'John', 'email' => ''],
		['name' => '', 'email' => 'jane@example.com'],
	], $repeater->getValues('array'));
});


test('setValues creates items', function () {
	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addText('email');
	});

	$repeater->setValues([
		['name' => 'John', 'email' => 'john@example.com'],
		['name' => 'Jane', 'email' => 'jane@example.com'],
	]);

	Assert::count(2, $repeater->getComponents());
	Assert::same('John', $repeater->getComponent('0')['name']->getValue());
	Assert::same('Jane', $repeater->getComponent('1')['name']->getValue());
});


test('setBounds creates default items on unsubmitted form', function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';

	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->setBounds(min: 1, max: 5, default: 2);

	Assert::count(2, $repeater->getComponents());
});


test('setBounds with only min creates min items on unsubmitted form', function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';

	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->setBounds(min: 3);

	Assert::count(3, $repeater->getComponents());
});


test('setBounds does not pad submitted items with empty ones', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'John'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->setBounds(min: 3);

	// the count violation is reported by validation, not fixed up silently
	Assert::count(1, $repeater->getComponents());
});


testException('setItemsCount throws when default exceeds max', function () {
	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->setBounds(min: 1, max: 3, default: 5);
}, Nette\InvalidArgumentException::class, 'Default items count (5) cannot be greater than maximum (3).');


test('nested repeater submission', function () {
	$_POST = ['persons' => [
		0 => [
			'name' => 'John',
			'emails' => [
				0 => ['address' => 'john1@example.com'],
				1 => ['address' => 'john2@example.com'],
			],
		],
		1 => [
			'name' => 'Jane',
			'emails' => [
				1 => ['address' => 'jane@example.com'],
			],
		],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addRepeater('emails', function ($emailContainer) {
			$emailContainer->addText('address');
		});
	});

	Assert::true($form->isValid());
	Assert::same([
		[
			'name' => 'John',
			'emails' => [
				['address' => 'john1@example.com'],
				['address' => 'john2@example.com'],
			],
		],
		[
			'name' => 'Jane',
			'emails' => [
				['address' => 'jane@example.com'],
			],
		],
	], $repeater->getValues('array'));
});


test('nested repeater submission and early binding', function () {
	$_POST = ['persons' => [
		0 => [
			'name' => 'John',
			'emails' => [
				0 => ['address' => 'john1@example.com'],
				1 => ['address' => 'john2@example.com'],
			],
		],
		1 => [
			'name' => 'Jane',
			'emails' => [
				1 => ['address' => 'jane@example.com'],
			],
		],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addRepeater('emails', function ($emailContainer) {
			$emailContainer->addText('address');
		})->setBounds(min: 1, max: 3);
	});

	Assert::true($form->isValid());
	Assert::same([
		[
			'name' => 'John',
			'emails' => [
				['address' => 'john1@example.com'],
				['address' => 'john2@example.com'],
			],
		],
		[
			'name' => 'Jane',
			'emails' => [
				['address' => 'jane@example.com'],
			],
		],
	], $repeater->getValues('array'));
});


test('repeater within container', function () {
	$_POST = ['user' => [
		'addresses' => [
			0 => ['street' => 'Main St'],
			1 => ['street' => 'Oak Ave'],
		],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$container = $form->addContainer('user');
	$repeater = $container->addRepeater('addresses', function ($addressContainer) {
		$addressContainer->addText('street');
	});

	Assert::true($form->isValid());
	Assert::count(2, $repeater->getComponents());
	Assert::same([
		['street' => 'Main St'],
		['street' => 'Oak Ave'],
	], $repeater->getValues('array'));
});


test('exceeding max count keeps items and invalidates the form', function () {
	$_POST = ['persons' => [
		0 => ['name' => 'Person 1'],
		1 => ['name' => 'Person 2'],
		2 => ['name' => 'Person 3'],
		3 => ['name' => 'Person 4'],
		4 => ['name' => 'Person 5'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->setBounds(max: 3);

	Assert::false($form->isValid());
	Assert::same(['Please add no more than 3 items.'], $form->getErrors());
	// submitted data are not truncated, the user can fix them
	Assert::count(5, $repeater->getComponents());
	Assert::same([
		['name' => 'Person 1'],
		['name' => 'Person 2'],
		['name' => 'Person 3'],
		['name' => 'Person 4'],
		['name' => 'Person 5'],
	], $repeater->getUntrustedValues('array'));
});


test('non-array data is ignored', function () {
	$_POST = ['persons' => 'not an array'];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	Assert::true($form->isValid());
	Assert::count(0, $repeater->getComponents());
	Assert::same([], $repeater->getValues('array'));
});


test('setValues without erase appends items', function () {
	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	// First set some values
	$repeater->setValues([
		['name' => 'John'],
		['name' => 'Jane'],
	]);

	Assert::count(2, $repeater->getComponents());

	// Set more values without erase
	$repeater->setValues([
		['name' => 'Bob'],
	]);

	// Should still have 2 items (setValues without erase doesn't add more)
	Assert::count(2, $repeater->getComponents());
	Assert::same('Bob', $repeater->getComponent('0')['name']->getValue());
	Assert::same('Jane', $repeater->getComponent('1')['name']->getValue());
});


test('gaps in indexes are filled sequentially', function () {
	$_POST = ['persons' => [
		10 => ['name' => 'First'],
		100 => ['name' => 'Second'],
		5 => ['name' => 'Third'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	Assert::true($form->isValid());
	Assert::count(3, $repeater->getComponents());

	// Should be renumbered as 0, 1, 2
	Assert::same('First', $repeater->getComponent('0')['name']->getValue());
	Assert::same('Second', $repeater->getComponent('1')['name']->getValue());
	Assert::same('Third', $repeater->getComponent('2')['name']->getValue());
});


test('mixed string and numeric keys are handled', function () {
	$_POST = ['persons' => [
		'0' => ['name' => 'First'],
		1 => ['name' => 'Second'],
		'foo' => ['name' => 'Third'],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	Assert::true($form->isValid());

	// String keys are treated as 0 in sorting, so order depends on implementation
	// After array_values(), should have continuous numeric keys
	Assert::count(3, $repeater->getComponents());
	$values = $repeater->getValues('array');
	Assert::count(3, $values);
});


test('deeply nested repeaters renumber correctly', function () {
	$_POST = ['companies' => [
		0 => [
			'name' => 'Company A',
			'departments' => [
				5 => [
					'name' => 'Dept 1',
					'employees' => [
						10 => ['name' => 'Employee 1'],
						2 => ['name' => 'Employee 2'],
					],
				],
			],
		],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('companies', function ($company) {
		$company->addText('name');
		$company->addRepeater('departments', function ($dept) {
			$dept->addText('name');
			$dept->addRepeater('employees', function ($employee) {
				$employee->addText('name');
			});
		});
	});

	Assert::true($form->isValid());

	$company = $repeater->getComponent('0');
	$dept = $company['departments']->getComponent('0');

	Assert::count(2, $dept['employees']->getComponents());
	Assert::same('Employee 1', $dept['employees']->getComponent('0')['name']->getValue());
	Assert::same('Employee 2', $dept['employees']->getComponent('1')['name']->getValue());
});


test('empty nested repeater data', function () {
	$_POST = ['persons' => [
		0 => [
			'name' => 'John',
			'emails' => [],
		],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addRepeater('emails', function ($emailContainer) {
			$emailContainer->addText('address');
		});
	});

	Assert::true($form->isValid());
	Assert::count(1, $repeater->getComponents());

	$person = $repeater->getComponent('0');
	Assert::count(0, $person['emails']->getComponents());

	Assert::same([
		[
			'name' => 'John',
			'emails' => [],
		],
	], $repeater->getValues('array'));
});


test('third-party controls read data through the layered path inside repeater', function () {
	$_POST = ['persons' => [
		// non-sequential indexes get renumbered, controls must still read their own data
		5 => ['date' => ['day' => '7', 'items' => ['a', 'b']]],
		9 => ['date' => ['day' => '8', 'items' => []]],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container['date'] = new class extends Nette\Forms\Controls\BaseControl {
			public string $day = '';
			public array $list = [];


			public function loadHttpData(): void
			{
				$this->day = @$this->getHttpData(Form::DataLine, '[day]') ?? ''; // @ - deprecated
				$this->list = @$this->getHttpData(Form::DataLine, '[items][]'); // @ - deprecated
			}
		};
	});

	Assert::true($form->isValid());
	Assert::same('7', $repeater->getComponent('0')['date']->day);
	Assert::same(['a', 'b'], $repeater->getComponent('0')['date']->list);
	Assert::same('8', $repeater->getComponent('1')['date']->day);
	Assert::same([], $repeater->getComponent('1')['date']->list);
});


testException('controls cannot be added directly to repeater', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});
	$repeater->addText('oops');
}, Nette\InvalidStateException::class, 'Repeater accepts only item containers, add controls in the factory passed to addRepeater().');
