<?php

/**
 * Test: FormMacros.
 */

use Nette\Forms\Form;
use Nette\Bridges\FormsLatte\FormMacros;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class MyControl extends Nette\Forms\Controls\BaseControl
{
	function getLabel($c = NULL)
	{
		return '<label>My</label>';
	}

	function getControl()
	{
		return '<input name=My>';
	}
}


$form = new Form;
$form->addHidden('id');
$form->addText('username', 'Username:'); // must have just one textfield to generate IE fix
$form->addRadioList('sex', 'Sex:', ['m' => 'male', 'f' => 'female']);
$form->addSelect('select', NULL, ['m' => 'male', 'f' => 'female']);
$form->addTextArea('area', NULL)->setValue('one<two');
$form->addCheckbox('checkbox', 'Checkbox');
$form->addCheckboxList('checklist', 'CheckboxList:', ['m' => 'male', 'f' => 'female']);
$form->addSubmit('send', 'Sign in');
$form['my'] = new MyControl;

$latte = new Latte\Engine;
FormMacros::install($latte->getCompiler());

$form['username']->addError('error');

Assert::matchFile(
	__DIR__ . '/expected/FormMacros.forms.phtml',
	$latte->compile(__DIR__ . '/templates/forms.latte')
);
Assert::matchFile(
	__DIR__ . '/expected/FormMacros.forms.html',
	$latte->renderToString(
		__DIR__ . '/templates/forms.latte',
		['_control' => ['myForm' => $form]]
	)
);
