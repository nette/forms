<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Controls\TextInput.
 */

use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class Translator implements Nette\Localization\ITranslator
{
	public function translate($s, ...$parameters): string
	{
		return strtoupper($s);
	}
}


test('basic input rendering', function () {
	$form = new Form;
	$input = $form->addText('text', 'Label')
		->setValue('text')
		->setHtmlAttribute('autocomplete', 'off');

	Assert::type(Html::class, $input->getLabel());
	Assert::same('<label for="frm-text">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="text" name="text" autocomplete="off" id="frm-text" value="text">', (string) $input->getControl());
});


test('translated attributes', function () {
	$form = new Form;
	$input = $form->addText('text', 'Label')
		->setHtmlAttribute('placeholder', 'place')
		->setValue('text')
		->setTranslator(new Translator)
		->setEmptyValue('xxx');

	Assert::same('<label for="frm-text">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text">Another label</label>', (string) $input->getLabel('Another label'));
	Assert::same(
		'<input type="text" name="text" placeholder="PLACE" id="frm-text" data-nette-empty-value="XXX" value="text">',
		(string) $input->getControl(),
	);
});


test('HTML label handling', function () {
	$form = new Form;
	$input = $form->addText('text', Html::el('b', 'Label'))
		->setTranslator(new Translator);

	Assert::same('<label for="frm-text"><b>Label</b></label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
});


test('required rule with HTML', function () {
	$form = new Form;
	$input = $form->addText('text', Html::el('b', 'Label:'))
		->addRule(Form::Required, 'Please fill in %label');

	Assert::same('<label for="frm-text"><b>Label:</b></label>', (string) $input->getLabel());
	Assert::same('<label for="frm-text"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
	Assert::type(Html::class, $input->getControl());
	Assert::same(
		'<input type="text" name="text" id="frm-text" required data-nette-rules=\'[{"op":":filled","msg":"Please fill in Label"}]\'>',
		(string) $input->getControl(),
	);
});


test('password input rendering', function () {
	$form = new Form;
	$input = $form->addPassword('password')
		->setValue('xxx');

	Assert::same('<input type="password" name="password" id="frm-password">', (string) $input->getControl());
});


test('input types and patterns', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setRequired('required')
		->addRule($form::Pattern, 'error message', '[0-9]+');

	foreach (['text', 'search', 'tel', 'url', 'email'] as $type) {
		$input->setHtmlType($type);
		Assert::same(
			'<input type="'
			. $type
			. '" name="text" id="frm-text" required pattern="[0-9]+" data-nette-rules=\'[{"op":":filled","msg":"required"},{"op":":pattern","msg":"error message","arg":"[0-9]+"}]\'>',
			(string) $input->getControl(),
		);
	}

	$input->setHtmlType('password');
	Assert::same(
		'<input type="password" name="text" id="frm-text" required pattern="[0-9]+" data-nette-rules=\'[{"op":":filled","msg":"required"},{"op":":pattern","msg":"error message","arg":"[0-9]+"}]\'>',
		(string) $input->getControl(),
	);

	$input->setHtmlType('number');
	Assert::same(
		'<input type="number" name="text" id="frm-text" required data-nette-rules=\'[{"op":":filled","msg":"required"},{"op":":pattern","msg":"error message","arg":"[0-9]+"}]\'>',
		(string) $input->getControl(),
	);
});


test('nested validation rules', function () {
	$form = new Form;
	$input = $form->addText('text');
	$input->addCondition($form::Filled)
		->addRule($form::Filled);

	Assert::same(
		'<input type="text" name="text" id="frm-text" data-nette-rules=\'[{"op":":filled","rules":[{"op":":filled","msg":"This field is required."}],"control":"text"}]\'>',
		(string) $input->getControl(),
	);
});


test('max length attribute', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setMaxLength(30);

	Assert::same('<input type="text" name="text" maxlength="30" id="frm-text">', (string) $input->getControl());
});


test('length rule precedence', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setMaxLength(30)
		->addRule($form::Length, null, [10, 20]);

	Assert::same(
		'<input type="text" name="text" maxlength="20" id="frm-text" data-nette-rules=\'[{"op":":length","msg":"Please enter a value between 10 and 20 characters long.","arg":[10,20]}]\'>',
		(string) $input->getControl(),
	);
});


test('max length rule application', function () {
	$form = new Form;
	$input = $form->addText('text', null, null, 30)
		->addRule($form::MaxLength, null, 10);

	Assert::same(
		'<input type="text" name="text" maxlength="10" id="frm-text" data-nette-rules=\'[{"op":":maxLength","msg":"Please enter no more than 10 characters.","arg":10}]\'>',
		(string) $input->getControl(),
	);
});


test('length bounds referencing a control', function () {
	$form = new Form;
	$min = $form->addInteger('min')->setValue(3);
	$input = $form->addText('text')->setValue('ab')
		->addRule($form::Length, 'err', [$min, 5]);

	$input->validate();
	Assert::same(['err'], $input->getErrors());
});


test('range and Min/Max rules', function () {
	$form = new Form;
	$minInput = $form->addText('min');
	$maxInput = $form->addText('max');
	$input = $form->addText('count')
		->addRule(Form::Range, 'Must be in range from %d to %d', [0, 100])
		->addRule(Form::Min, 'Must be greater than or equal to %d', 1)
		->addRule(Form::Max, 'Must be less than or equal to %d', 101)
		->addRule(Form::Range, 'Must be in range from %d to %d', [$minInput, $maxInput]);

	Assert::same(
		'<input type="text" name="count" id="frm-count" data-nette-rules=\'[{"op":":range","msg":"Must be in range from 0 to 100","arg":[0,100]},{"op":":min","msg":"Must be greater than or equal to 1","arg":1},{"op":":max","msg":"Must be less than or equal to 101","arg":101},{"op":":range","msg":"Must be in range from %0 to %1","arg":[{"control":"min"},{"control":"max"}]}]\'>',
		(string) $input->getControl(),
	);
});


test('number input rules', function () {
	$form = new Form;
	$minInput = $form->addText('min');
	$maxInput = $form->addText('max');
	$input = $form->addText('count')
		->setHtmlType('number')
		->addRule(Form::Range, 'Must be in range from %d to %d', [0, 100])
		->addRule(Form::Min, 'Must be greater than or equal to %d', 1)
		->addRule(Form::Max, 'Must be less than or equal to %d', 101)
		->addRule(Form::Range, 'Must be in range from %d to %d', [$minInput, $maxInput]);

	Assert::same(
		'<input type="number" name="count" id="frm-count" min="1" max="100" data-nette-rules=\'[{"op":":range","msg":"Must be in range from 0 to 100","arg":[0,100]},{"op":":min","msg":"Must be greater than or equal to 1","arg":1},{"op":":max","msg":"Must be less than or equal to 101","arg":101},{"op":":range","msg":"Must be in range from %0 to %1","arg":[{"control":"min"},{"control":"max"}]}]\'>',
		(string) $input->getControl(),
	);
});


test('empty value attribute', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setEmptyValue('empty ');

	Assert::same(
		'<input type="text" name="text" id="frm-text" data-nette-empty-value="empty" value="empty ">',
		(string) $input->getControl(),
	);
});


test('nullable input handling', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setNullable();

	Assert::null($input->getValue());
});


test('nullable empty value', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setEmptyValue('empty ')
		->setNullable();

	Assert::null($input->getValue());
	Assert::same(
		'<input type="text" name="text" id="frm-text" data-nette-empty-value="empty" value="empty ">',
		(string) $input->getControl(),
	);
});


test('default value rendering', function () {
	$form = new Form;
	$input = $form->addText('text')
		->setDefaultValue('default');

	Assert::same('<input type="text" name="text" id="frm-text" value="default">', (string) $input->getControl());
});


test('container input naming', function () {
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addText('text');

	Assert::same('<input type="text" name="container[text]" id="frm-container-text">', (string) $input->getControl());
});


test('control options handling', function () {
	$form = new Form;
	$input = $form->addText('text');

	Assert::same('text', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});


test('integer input rendering', function () {
	$form = new Form;
	$input = $form->addInteger('text');

	Assert::null($input->getValue());

	Assert::same(
		'<input type="number" name="text" id="frm-text" data-nette-rules=\'[{"op":":integer","msg":"Please enter a valid integer."}]\'>',
		(string) $input->getControl(),
	);
});


test('float input rendering', function () {
	$form = new Form;
	$input = $form->addFloat('text');

	Assert::null($input->getValue());

	Assert::same(
		'<input type="text" name="text" id="frm-text" inputmode="decimal" data-nette-rules=\'[{"op":":float","msg":"Please enter a valid number."}]\'>',
		(string) $input->getControl(),
	);
});


test('float rule on a plain text input adds only the decimal keyboard hint', function () {
	$form = new Form;
	$input = $form->addText('price')->addRule(Form::Float);

	Assert::same(
		'<input type="text" name="price" id="frm-price" inputmode="decimal" data-nette-rules=\'[{"op":":float","msg":"Please enter a valid number."}]\'>',
		(string) $input->getControl(),
	);
});


test('type and inputmode follow the rules at render time', function () {
	$form = new Form;
	$input = $form->addText('email');
	$input->getRules()->addRule(Form::Email);
	Assert::contains('type="email"', (string) $input->getControl());

	$input->getRules()->removeRule(Form::Email);
	Assert::contains('type="text"', (string) $input->getControl());

	$input->addRule(new Nette\Forms\Validation\IntegerValidator);
	Assert::contains('type="number"', (string) $input->getControl());

	$input->getRules()->reset();
	$input->addRule(Form::Float);
	Assert::contains('inputmode="decimal"', (string) $input->getControl());
	$input->getRules()->removeRule(Form::Float);
	Assert::notContains('inputmode', (string) $input->getControl());
});


test('an explicitly set type wins over the type of the rules', function () {
	$form = new Form;
	$input = $form->addPassword('password')->addRule(Form::Email);
	Assert::contains('type="password"', (string) $input->getControl());

	$input = $form->addText('code')->addRule(Form::Integer)->setHtmlType('text');
	Assert::contains('type="text"', (string) $input->getControl());

	// the decimal keyboard is only a hint for a text input
	$input = $form->addText('number')->setHtmlType('number')->addRule(Form::Float);
	Assert::notContains('inputmode', (string) $input->getControl());

	// the first rule declaring a type wins
	$input = $form->addText('web')->addRule(Form::URL)->addRule(Form::Email);
	Assert::contains('type="url"', (string) $input->getControl());
});


test('rules in conditions and after a mutating filter set no type', function () {
	$form = new Form;
	$input = $form->addText('email');
	$input->addCondition(Form::Filled)->addRule(Form::Email);
	Assert::contains('type="text"', (string) $input->getControl());

	$input = $form->addText('price')
		->addFilter(fn($s) => $s)
		->addRule(Form::Float)
		->addRule(Form::Integer);
	Assert::same('<input type="text" name="price" id="frm-price">', (string) $input->getControl());
});


test('addInteger() renders a number input with min and max of the rules', function () {
	$form = new Form;
	$input = $form->addInteger('age')->addRule(Form::Range, null, [18, 99]);

	Assert::same(
		'<input type="number" name="age" id="frm-age" min="18" max="99" data-nette-rules=\'[{"op":":integer","msg":"Please enter a valid integer."},{"op":":range","msg":"Please enter a value between 18 and 99.","arg":[18,99]}]\'>',
		(string) $input->getControl(),
	);
});


test('addUrl() sets the url type and validation', function () {
	$form = new Form;
	$input = $form->addUrl('web');

	Assert::same(
		'<input type="url" name="web" id="frm-web" data-nette-rules=\'[{"op":":url","msg":"Please enter a valid URL."}]\'>',
		(string) $input->getControl(),
	);
});


test('addPhone() sets the tel type and autofill hint, no validation', function () {
	$form = new Form;
	$input = $form->addPhone('phone');

	Assert::same('<input type="tel" name="phone" autocomplete="tel" id="frm-phone">', (string) $input->getControl());
});


test('rule and filter interaction', function () {
	$form = new Form;
	$input = $form->addText('text')
		->addRule(Form::Min, 'min', 1)
		->addFilter(function () {})
		->addRule(Form::Max, 'max', 10)
		->addRule(Form::MaxLength, 'maxl', 10);

	Assert::same(
		'<input type="text" name="text" id="frm-text" data-nette-rules=\'[{"op":":min","msg":"min","arg":1}]\'>',
		(string) $input->getControl(),
	);
});


test('email input rendering', function () {
	$form = new Form;
	$input = $form->addEmail('email', 'Label');

	Assert::type(Html::class, $input->getLabel());
	Assert::same('<label for="frm-email">Label</label>', (string) $input->getLabel());

	Assert::type(Html::class, $input->getControl());
	Assert::same(
		'<input type="email" name="email" maxlength="255" id="frm-email" data-nette-rules=\'[{"op":":email","msg":"Please enter a valid email address."}]\'>',
		(string) $input->getControl(),
	);
});


test('suggestions render as a datalist linked via the list attribute', function () {
	$form = new Form;
	$input = $form->addText('city')->setSuggestions(['Praha', 'Brno']);

	// the control itself stays a plain input, so renderers can style it as usual
	Assert::same(
		'<input type="text" name="city" id="frm-city" list="frm-city-list">',
		(string) $input->getControl(),
	);
	Assert::same(
		'<datalist id="frm-city-list"><option value="Praha"></option><option value="Brno"></option></datalist>',
		(string) $input->getDatalist(),
	);

	// the datalist of a rendered input is emitted before the form end, like hidden fields
	$rendered = $form->__toString(true);
	Assert::contains('list="frm-city-list"', $rendered);
	Assert::same(1, substr_count($rendered, '<datalist id="frm-city-list">'));
	Assert::true(strpos($rendered, '<datalist id="frm-city-list">') < strpos($rendered, '</form>'));

	// value => label pairs and translation
	$pairs = $form->addText('country')
		->setSuggestions(['CZ' => 'Czechia'])
		->setTranslator(new Translator);
	$pairs->getControl();
	Assert::same(
		'<datalist id="frm-country-list"><option value="CZ" label="CZECHIA"></option></datalist>',
		(string) $pairs->getDatalist(),
	);
});


test('suggestions: a list of values is not translated', function () {
	$form = new Form;
	$input = $form->addText('city')
		->setSuggestions(['Praha', 'Brno'])
		->setTranslator(new Translator);
	$input->getControl();
	Assert::same(
		'<datalist id="frm-city-list"><option value="Praha"></option><option value="Brno"></option></datalist>',
		(string) $input->getDatalist(),
	);
});


test('suggestions: numeric values with labels', function () {
	$form = new Form;
	$input = $form->addText('amount')
		->setSuggestions([10 => 'Ten', 20 => 'Twenty'])
		->setTranslator(new Translator);
	$input->getControl();
	Assert::same(
		'<datalist id="frm-amount-list"><option value="10" label="TEN"></option><option value="20" label="TWENTY"></option></datalist>',
		(string) $input->getDatalist(),
	);
});
