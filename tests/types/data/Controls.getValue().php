<?php

declare(strict_types=1);

use Nette\Forms\Form;
use function PHPStan\Testing\assertType;


$form = new Form;

$input = $form->addText('Text');
assertType('string|null', $input->getValue());

$input = $form->addPassword('Password');
assertType('string|null', $input->getValue());

$input = $form->addTextArea('TextArea');
assertType('string|null', $input->getValue());

$input = $form->addEmail('Email');
assertType('string|null', $input->getValue());

$input = $form->addInteger('Integer');
assertType('string|null', $input->getValue());

$input = $form->addUpload('Upload');
assertType('array<Nette\Http\FileUpload>|Nette\Http\FileUpload|null', $input->getValue());

$input = $form->addMultiUpload('MultiUpload');
assertType('array<Nette\Http\FileUpload>|Nette\Http\FileUpload|null', $input->getValue());

$input = $form->addHidden('Hidden');
assertType('string|null', $input->getValue());

$input = $form->addCheckbox('Checkbox');
assertType('bool|null', $input->getValue());

$input = $form->addRadioList('RadioList');
assertType('int|string|null', $input->getValue());

$input = $form->addCheckboxList('CheckboxList');
assertType('array<(int|string)>', $input->getValue());

$input = $form->addSelect('Select');
assertType('int|string|null', $input->getValue());

$input = $form->addMultiSelect('MultiSelect');
assertType('array<(int|string)>', $input->getValue());

$input = $form->addSubmit('Submit');
assertType('string|null', $input->getValue());

$input = $form->addButton('Button');
assertType('string|null', $input->getValue());

$input = $form->addImageButton('ImageButton');
assertType('array|null', $input->getValue());

$input = $form->addImage('Image');
assertType('array|null', $input->getValue());
