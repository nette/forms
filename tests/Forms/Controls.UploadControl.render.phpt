<?php

/**
 * Test: Nette\Forms\Controls\UploadControl.
 */

declare(strict_types=1);

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


test(function () {
	$form = new Form;
	$input = $form->addUpload('file', 'Label');

	Assert::type(Html::class, $input->getLabel());
	Assert::same('<label for="frm-file">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-file">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type(Html::class, $input->getControl());
	Assert::same('<input type="file" name="file" id="frm-file">', (string) $input->getControl());
});


test(function () { // multiple
	$form = new Form;
	$input = $form->addMultiUpload('file', 'Label');

	Assert::same('<input type="file" name="file[]" multiple id="frm-file">', (string) $input->getControl());
});


test(function () { // Html with translator
	$form = new Form;
	$input = $form->addUpload('file', 'Label');
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-file">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-file">Another label</label>', (string) $input->getLabel('Another label'));
	Assert::same('<label for="frm-file"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
});


test(function () { // validation rules
	$form = new Form;
	$input = $form->addUpload('file')->setRequired('required');

	Assert::same('<input type="file" name="file" id="frm-file" required data-nette-rules=\'[{"op":":filled","msg":"required"}]\'>', (string) $input->getControl());
});


test(function () { // accepted files
	$form = new Form;
	$input = $form->addUpload('file1')->addRule(Form::MIME_TYPE, null, 'image/*');
	Assert::same('<input type="file" name="file1" accept="image/*" id="frm-file1" data-nette-rules=\'[{"op":":mimeType","msg":"The uploaded file is not in the expected format.","arg":"image/*"}]\'>', (string) $input->getControl());

	$input = $form->addUpload('file2')->addRule(Form::MIME_TYPE, null, ['image/*', 'text/html']);
	Assert::same('<input type="file" name="file2" accept="image/*, text/html" id="frm-file2" data-nette-rules=\'[{"op":":mimeType","msg":"The uploaded file is not in the expected format.","arg":["image/*","text/html"]}]\'>', (string) $input->getControl());

	$input = $form->addUpload('file3')->addRule(Form::IMAGE);
	Assert::same('<input type="file" name="file3" accept="image/gif, image/png, image/jpeg, image/webp" id="frm-file3" data-nette-rules=\'[{"op":":image","msg":"The uploaded file must be image in format JPEG, GIF, PNG or WebP."}]\'>', (string) $input->getControl());
});


test(function () { // container
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addUpload('file');

	Assert::same('<input type="file" name="container[file]" id="frm-container-file">', (string) $input->getControl());
});


test(function () { // rendering options
	$form = new Form;
	$input = $form->addUpload('file');

	Assert::same('file', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});
