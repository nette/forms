<?php

/**
 * Test: FormMacros.
 */

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormMacros;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '>')) {
	Tester\Environment::skip('Test for Latte 2');
}


class MyControl extends Nette\Forms\Controls\BaseControl
{
	public function getLabel($c = null)
	{
		return '<label>My</label>';
	}


	public function getControl()
	{
		return '<input name=My>';
	}
}


$form = new Form;
$form->getElementPrototype()->addClass('form-class');
$form->addHidden('id');
$form->addText('username', 'Username:'); // must have just one textfield to generate IE fix
$form['username']->getControlPrototype()->addClass('control-class');
$form->addRadioList('sex', 'Sex:', ['m' => 'male', 'f' => 'female']);
$form->addSelect('select', null, ['m' => 'male', 'f' => 'female']);
$form->addTextArea('area', null)->setValue('one<two');
$form->addCheckbox('checkbox', 'Checkbox');
$form->addCheckboxList('checklist', 'CheckboxList:', ['m' => 'male', 'f' => 'female']);
$form->addSubmit('send', 'Sign in');
$form['my'] = new MyControl;

$latte = new Latte\Engine;
FormMacros::install($latte->getCompiler());
$latte->addProvider('uiControl', ['myForm' => $form]);

$form['username']->addError('error');

Assert::matchFile(
	__DIR__ . '/expected/FormMacros.forms.php',
	$latte->compile(__DIR__ . '/templates/forms.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/FormMacros.forms.html',
	$latte->renderToString(__DIR__ . '/templates/forms.latte')
);
