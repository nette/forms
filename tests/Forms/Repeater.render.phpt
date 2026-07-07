<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Repeater rendering.
 */

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

	Assert::same(
		'<input type="text" name="persons[0][emails][0][address]" id="frm-persons-0-emails-0-address">',
		(string) $emailItem['address']->getControl(),
	);
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

	Assert::same(
		'<input type="text" name="user[addresses][0][street]" id="frm-user-addresses-0-street">',
		(string) $item0['street']->getControl(),
	);
});


test('render method generates HTML structure', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->setBounds(min: 2, max: 5);

	$output = $repeater->render(function ($container) {
		echo "Item content\n";
	});

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


test('setBounds creates default items immediately, render has no side-effects', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->setBounds(max: 5, default: 3);

	// default items exist right after setBounds()
	Assert::count(3, $repeater->getComponents());

	$output = $repeater->render(function ($container) {
		echo "Item\n";
	});

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

	$output = $repeater->render(function ($container) {});

	Assert::contains('class="custom-repeater"', $output);
});


test('DefaultFormRenderer renders repeater items as a static block', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name', 'Name');
		$container->addRepeater('emails', function ($email) {
			$email->addText('address', 'Address');
		})->setBounds(min: 1);
	});
	$repeater->setBounds(default: 2);
	$form->addText('note', 'Note');
	$form->addSubmit('send', 'Send');

	$output = $form->getRenderer()->render($form);

	// items are grouped in a visually delimited block, including the nested repeater
	Assert::contains('<fieldset class="repeater">', $output);
	Assert::contains('name="persons[0][name]"', $output);
	Assert::contains('name="persons[1][name]"', $output);
	Assert::contains('name="persons[0][emails][0][address]"', $output);
	Assert::contains('name="note"', $output);

	// no dynamic parts: no template, no JS attributes
	Assert::notContains('<template>', $output);
	Assert::notContains('data-nette-repeater', $output);
});


test('item wrapper is rendered from the item prototype and is part of the template', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});
	$repeater->setBounds(min: 1);
	$repeater->getContainerPrototype()->setName('tbody');
	$repeater->getItemPrototype()->setName('tr')->class('invoice-row');

	$output = $repeater->render(function ($container) {
		echo 'X';
	});

	Assert::contains('<tbody data-nette-repeater="persons"', $output);
	Assert::contains('<tr class="invoice-row" data-repeater-index="0">X</tr>', $output);
	// the template contains the wrapper too, with an empty index to be filled by JS
	Assert::contains("<template>\n<tr class=\"invoice-row\" data-repeater-index=\"\">\nX</tr>\n</template>", $output);
});


test('createTemplate returns container without real form', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
		$container->addText('email')
			->setDefaultValue('example.com');
	});

	// Use reflection to call private createTemplate method
	$reflection = new ReflectionClass($repeater);
	$method = $reflection->getMethod('createTemplate');
	$template = $method->invoke($repeater);

	Assert::type(Nette\Forms\Container::class, $template);
	Assert::true(isset($template['name']));
	Assert::true(isset($template['email']));

	// Template controls should have relative names and keep factory defaults
	Assert::same('<input type="text" name="[name]" id="frm--name">', (string) $template['name']->getControl());
	Assert::same('<input type="text" name="[email]" id="frm--email" value="example.com">', (string) $template['email']->getControl());
});


test('add button prototype carries the repeater path', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$button = $repeater->getAddButtonPrototype();

	Assert::type(Html::class, $button);
	Assert::same('button', $button->getName());
	Assert::same('button', $button->getAttribute('type'));
	Assert::same('persons', $button->getAttribute('data-nette-repeater-add'));
});


test('nested add button prototype carries the full path', function () {
	$form = new Form;
	$outer = $form->addContainer('user')->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	Assert::same('user-persons', $outer->getAddButtonPrototype()->getAttribute('data-nette-repeater-add'));
});


test('remove button prototype', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$button = $repeater->getRemoveButtonPrototype();

	Assert::type(Html::class, $button);
	Assert::same('button', $button->getName());
	Assert::same('button', $button->getAttribute('type'));
	Assert::same('true', $button->getAttribute('data-nette-repeater-remove'));
});


test('button prototypes are customizable', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});

	$repeater->getAddButtonPrototype()
		->setText('Custom Add')
		->class('custom-class');

	$button = $repeater->getAddButtonPrototype();
	Assert::contains('Custom Add', (string) $button);
	Assert::contains('class="custom-class"', (string) $button);
});
