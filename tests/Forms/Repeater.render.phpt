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


test('DefaultFormRenderer renders a working repeater', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name', 'Name');
		$container->addHidden('id');
		$container->addRepeater('emails', function ($email) {
			$email->addText('address', 'Address');
		})->setBounds(min: 1);
	});
	$repeater->setBounds(default: 1);
	$repeater->getAddButtonPrototype()->setText('Add person');
	$form->addText('note', 'Note');
	$form->addRepeater('empty', fn($c) => $c->addText('x'));
	$form->addSubmit('send', 'Send');

	Assert::matchFile(__DIR__ . '/expected/Repeater.renderer.html', $form->__toString());
});


test('DefaultFormRenderer wrapper of the repeater is configurable', function () {
	$form = new Form;
	$form->addRepeater('persons', fn($c) => $c->addText('name'));
	$renderer = $form->getRenderer();
	assert($renderer instanceof Nette\Forms\Rendering\DefaultFormRenderer);
	$renderer->wrappers['repeater']['container'] = 'div class=rows';
	$renderer->wrappers['controls']['container'] = null;
	$renderer->wrappers['pair']['container'] = 'p';

	Assert::contains('<div class="rows" data-nette-repeater="persons" data-nette-repeater-min="0">', $form->__toString());
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
	// the template contains the wrapper too, its index is a placeholder replaced by JS
	Assert::contains("<template>\n<tr class=\"invoice-row\" data-repeater-index=\"__index0__\">X</tr>\n</template>", $output);
});


test('template lives in the real form but is not an item', function () {
	$form = new Form('order');
	$form->setTranslator(new class implements Nette\Localization\Translator {
		public function translate(string|Stringable $message, mixed ...$parameters): string
		{
			return strtoupper((string) $message);
		}
	});
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name', 'Name')
			->setHtmlAttribute('placeholder', 'Your name')
			->setRequired('Fill name');
		$container->addText('email')
			->setDefaultValue('example.com')
			->setSuggestions(['a@b.cz']);
	});

	$template = $repeater->getTemplate();
	Assert::same($form, $template->getForm());
	Assert::count(0, $repeater->getComponents());
	Assert::count(0, iterator_to_array($form->getControls()));
	Assert::same([], $form->getValues('array')['persons']);

	Assert::same('<label for="frm-order-persons-__index0__-name">NAME</label>', (string) $template['name']->getLabel());
	Assert::same(
		'<input type="text" name="persons[__index0__][name]" placeholder="YOUR NAME" id="frm-order-persons-__index0__-name" required data-nette-rules=\'[{"op":":filled","msg":"FILL NAME"}]\'>',
		(string) $template['name']->getControl(),
	);
	Assert::same(
		'<input type="text" name="persons[__index0__][email]" id="frm-order-persons-__index0__-email" value="example.com" list="frm-order-persons-__index0__-email-list">',
		(string) $template['email']->getControl(),
	);

	$output = $repeater->renderItems(function ($container) {
		echo $container['email']->getControl();
	});
	Assert::contains(
		'<datalist id="frm-order-persons-__index0__-email-list"><option value="a&#64;b.cz"></option></datalist></div>',
		$output,
	);
});


test('template follows the id mask', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', fn($c) => $c->addText('name'));
	$mask = Nette\Forms\Controls\BaseControl::$idMask;
	Nette\Forms\Controls\BaseControl::$idMask = 'x-%s';
	try {
		Assert::same('x-persons-__index0__-name', $repeater->getTemplate()['name']->getHtmlId());
	} finally {
		Nette\Forms\Controls\BaseControl::$idMask = $mask;
	}
});


test('upload in the template makes the form multipart', function () {
	$form = new Form;
	$form->addRepeater('files', fn($c) => $c->addUpload('file'));

	Assert::same('multipart/form-data', $form->getElementPrototype()->enctype);
});


test('template keeps factory defaults on a submitted form and loads no data', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = ['persons' => [['name' => 'John']], 'abs' => 'forged'];
	$form = new Form;
	$form->allowCrossOrigin();
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addText('name')->setDefaultValue('?')->setRequired();
		$container->addText('abs')->setHtmlAttribute('name', 'abs')->setDefaultValue('d');
	});

	Assert::true($form->isValid());
	Assert::same('?', $repeater->getTemplate()['name']->getValue());
	Assert::same([['name' => 'John', 'abs' => 'forged']], $repeater->getValues('array'));
	Assert::same('d', $repeater->getTemplate()['abs']->getValue());
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_POST = [];
});


test('nested templates use distinct placeholders', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addRepeater('emails', fn($c) => $c->addText('address'))->setBounds(default: 1);
	});

	$emails = $repeater->getTemplate()['emails'];
	Assert::same('persons[__index0__][emails][0][address]', $emails['0']['address']->getHtmlName());
	Assert::same('persons[__index0__][emails][__index1__][address]', $emails->getTemplate()['address']->getHtmlName());
	Assert::same('persons-__index0__-emails', $emails->getAddButtonPrototype()->getAttribute('data-nette-repeater-add'));
});


test('hidden fields are rendered inside their item', function () {
	$form = new Form;
	$repeater = $form->addRepeater('persons', function ($container) {
		$container->addHidden('id', 5);
		$container->addText('name');
	}, default: 1);

	$output = $repeater->renderItems(function ($container) {
		echo $container['name']->getControl();
	});
	Assert::contains('id="frm-persons-0-name"><input type="hidden" name="persons[0][id]" value="5"></div>', $output);
	Assert::notContains('persons[0][id]', $form->getRenderer()->render($form, 'end'));
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
