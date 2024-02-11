<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->getElementPrototype()->id = 'frmid';

$form->addText('a');
$form->addTextArea('b');
$form->addDate('c');
$form->addUpload('d');
$form->addHidden('e');
$form->addCheckbox('f');
$form->addRadioList('g', null, ['item']);
$form->addCheckboxList('h', null, ['item']);
$form->addSelect('i');
$form->addMultiSelect('j');
$form->addColor('k');
$form->addSubmit('l');
$form->addButton('m');
$form->addImageButton('n');
$form->addHidden('none')->setHtmlAttribute('form', false);
$form->addHidden('diff')->setHtmlAttribute('form', 'different');

Assert::matchFile(__DIR__ . '/expected/Forms.form.expect', $form->__toString(true));
