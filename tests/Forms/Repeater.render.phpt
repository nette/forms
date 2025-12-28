<?php

/**
 * Test: Nette\Forms\Repeater rendering.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('item controls rendering', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addText('email');
	});

	$repeater->setBounds(min: 2);

	// TODO: check $repeater->render();
	$item0 = $repeater->getComponent('0');
	$item1 = $repeater->getComponent('1');

	Assert::same('<input type="text" name="persons[0][name]" id="frm-persons-0-name">', (string) $item0['name']->getControl());
	Assert::same('<input type="text" name="persons[0][email]" id="frm-persons-0-email">', (string) $item0['email']->getControl());

	Assert::same('<input type="text" name="persons[1][name]" id="frm-persons-1-name">', (string) $item1['name']->getControl());
	Assert::same('<input type="text" name="persons[1][email]" id="frm-persons-1-email">', (string) $item1['email']->getControl());
});


test('item controls with values', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->setValues([
		['name' => 'John'],
		['name' => 'Jane'],
	]);

	$item0 = $repeater->getComponent('0');
	$item1 = $repeater->getComponent('1');

	Assert::same('<input type="text" name="persons[0][name]" id="frm-persons-0-name" value="John">', (string) $item0['name']->getControl());
	Assert::same('<input type="text" name="persons[1][name]" id="frm-persons-1-name" value="Jane">', (string) $item1['name']->getControl());
});


test('nested repeater controls rendering', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addRepeater('emails', function ($emailContainer) {
			$emailContainer->addText('address');
		});
	});

	$repeater->setBounds(min: 1);
	$person0 = $repeater->getComponent('0');
	$person0['emails']->setBounds(min: 1);

	$emailItem = $person0['emails']->getComponent('0');

	Assert::same('<input type="text" name="persons[0][emails][0][address]" id="frm-persons-0-emails-0-address">', (string) $emailItem['address']->getControl());
});


test('required validation in item controls', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name')
			->setRequired('Name is required');
	});

	$repeater->setBounds(min: 1);
	$item0 = $repeater->getComponent('0');

	Assert::contains('data-nette-rules', (string) $item0['name']->getControl());
	Assert::contains('"op":":filled"', (string) $item0['name']->getControl());
});


test('repeater within container rendering', function () {
	$form = new Form;
	$container = $form->addContainer('user');
	$repeater = $container->addRepeater('addresses', function ($addressContainer) {
		$addressContainer->addText('street');
	});

	$repeater->setBounds(min: 1);
	$item0 = $repeater->getComponent('0');

	Assert::same('<input type="text" name="user[addresses][0][street]" id="frm-user-addresses-0-street">', (string) $item0['street']->getControl());
});


test('render method generates HTML structure', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->setBounds(min: 2, max: 5);

	ob_start();
	$repeater->render(function ($container) {
		echo "Item content\n";
	});
	$output = ob_get_clean();

	// Check container attributes
	Assert::contains('data-nette-repeater="persons"', $output);
	Assert::contains('data-nette-repeater-min="2"', $output);
	Assert::contains('data-nette-repeater-max="5"', $output);

	// Check items
	Assert::contains('data-repeater-index="0"', $output);
	Assert::contains('data-repeater-index="1"', $output);

	// Check template
	Assert::contains('<template>', $output);
	Assert::contains('</template>', $output);
});


test('render with default count creates items on render', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->setBounds(min: 0, max: 5, default: 3);

	// Before render, should have min items (0)
	Assert::count(0, $repeater->getComponents());

	ob_start();
	$repeater->render(function ($container) {
		echo "Item\n";
	});
	$output = ob_get_clean();

	// After render, should have default items (3)
	Assert::count(3, $repeater->getComponents());
	Assert::contains('data-repeater-index="0"', $output);
	Assert::contains('data-repeater-index="1"', $output);
	Assert::contains('data-repeater-index="2"', $output);
});


test('getContainerPrototype returns Html element', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$prototype = $repeater->getContainerPrototype();

	Assert::type(Html::class, $prototype);
	Assert::same('div', $prototype->getName());
});


test('getContainerPrototype can be customized', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->getContainerPrototype()->class('custom-repeater');

	ob_start();
	$repeater->render(function ($container) {});
	$output = ob_get_clean();

	Assert::contains('class="custom-repeater"', $output);
});


test('createTemplate returns container without real form', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addText('email');
	});

	// Use reflection to call private createTemplate method
	$reflection = new ReflectionClass($repeater);
	$method = $reflection->getMethod('createTemplate');
	$template = $method->invoke($repeater);

	Assert::type(Nette\Forms\Container::class, $template);
	Assert::true(isset($template['name']));
	Assert::true(isset($template['email']));

	// Template controls should have relative names
	Assert::same('<input type="text" name="[name]" id="frm--name">', (string) $template['name']->getControl());
	Assert::same('<input type="text" name="[email]" id="frm--email">', (string) $template['email']->getControl());
});


test('defineButton creates button element', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$button = $repeater->defineButton('custom', 'Custom Button');

	Assert::type(Html::class, $button);
	Assert::same('button', $button->getName());
	Assert::same('Custom Button', $button->getText());
});


test('defineButton allows customization', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$button = $repeater->defineButton('add', 'Add Item');
	$button->class('btn btn-primary');

	// Button should be customized
	Assert::contains('class="btn btn-primary"', (string) $button);
});


test('getButtonControl returns add button', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$button = $repeater->getButtonControl('add');

	Assert::type(Html::class, $button);
	Assert::same('button', $button->getName());
	Assert::same('button', $button->getAttribute('type'));
	Assert::same('persons', $button->getAttribute('data-nette-repeater-add'));
});


test('getButtonControl returns remove button', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$button = $repeater->getButtonControl('remove');

	Assert::type(Html::class, $button);
	Assert::same('button', $button->getName());
	Assert::same('button', $button->getAttribute('type'));
	Assert::same('true', $button->getAttribute('data-nette-repeater-remove'));
});


test('getButtonControl uses custom defined button', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->defineButton('add', 'Custom Add')->class('custom-class');

	$button = $repeater->getButtonControl('add');

	Assert::contains('Custom Add', (string) $button);
	Assert::contains('class="custom-class"', (string) $button);
});
