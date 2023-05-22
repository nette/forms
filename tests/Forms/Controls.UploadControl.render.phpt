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


test('', function () {
	$form = new Form;
	$input = $form->addUpload('file', 'Label');

	Assert::type(Html::class, $input->getLabel());
	Assert::same('<label for="frm-file">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-file">Another label</label>', (string) $input->getLabel('Another label'));

	Assert::type(Html::class, $input->getControl());
	Assert::match('<input type="file" name="file" id="frm-file" data-nette-rules=\'[{"op":":fileSize","msg":"The size of the uploaded file can be up to %d% bytes.","arg":%d%}]\'>', (string) $input->getControl());
});


test('multiple', function () {
	$form = new Form;
	$input = $form->addMultiUpload('file', 'Label');

	Assert::match('<input type="file" name="file[]" multiple id="frm-file" data-nette-rules=%a%>', (string) $input->getControl());
});


test('Html with translator', function () {
	$form = new Form;
	$input = $form->addUpload('file', 'Label');
	$input->setTranslator(new Translator);

	Assert::same('<label for="frm-file">Label</label>', (string) $input->getLabel());
	Assert::same('<label for="frm-file">Another label</label>', (string) $input->getLabel('Another label'));
	Assert::same('<label for="frm-file"><b>Another label</b></label>', (string) $input->getLabel(Html::el('b', 'Another label')));
});


test('validation rules', function () {
	$form = new Form;
	$input = $form->addUpload('file')->setRequired('required');
	Assert::match('<input type="file" name="file" id="frm-file" required data-nette-rules=\'[{"op":":filled","msg":"required"},{"op":":fileSize",%a%}]\'>', (string) $input->getControl());
});


test('accepted files', function () {
	$form = new Form;
	$input = $form->addUpload('file1')->addRule(Form::MimeType, null, 'image/*');
	Assert::match('<input type="file" name="file1" accept="image/*" id="frm-file1" data-nette-rules=\'[{"op":":fileSize",%a%},{"op":":mimeType","msg":"The uploaded file is not in the expected format.","arg":"image/*"}]\'>', (string) $input->getControl());

	$input = $form->addUpload('file2')->addRule(Form::MimeType, null, ['image/*', 'text/html']);
	Assert::match('<input type="file" name="file2" accept="image/*, text/html" id="frm-file2" data-nette-rules=\'[{"op":":fileSize",%a%},{"op":":mimeType","msg":"The uploaded file is not in the expected format.","arg":["image/*","text/html"]}]\'>', (string) $input->getControl());

	$input = $form->addUpload('file3')->addRule(Form::Image);
	Assert::match('<input type="file" name="file3" accept="image/gif, image/png, image/jpeg, image/webp, image/avif" id="frm-file3" data-nette-rules=\'[{"op":":fileSize",%a%},{"op":":image","msg":"The uploaded file must be image in format JPEG, GIF, PNG, WebP or AVIF."}]\'>', (string) $input->getControl());

    $input = $form->addUpload('file4')->addRule(Form::Image)->addRule(Form::MimeType, null, 'text/html');
    Assert::match('<input type="file" name="file4" accept="image/gif, image/png, image/jpeg, image/webp, image/avif, text/html" id="frm-file4" data-nette-rules=\'[{"op":":fileSize",%a%},{"op":":mimeType","msg":"The uploaded file is not in the expected format.","arg":["image/gif","image/png","image/jpeg","image/webp","image/avif","text/html"]}]\'>', (string) $input->getControl());

    $input = $form->addUpload('file5')->addRule(Form::MimeType, null, 'text/html')->addRule(Form::Image);
    Assert::match('<input type="file" name="file5" accept="text/html, image/gif, image/png, image/jpeg, image/webp, image/avif" id="frm-file5" data-nette-rules=\'[{"op":":fileSize",%a%},{"op":":mimeType","msg":"The uploaded file is not in the expected format.","arg":["text/html","image/gif","image/png","image/jpeg","image/webp","image/avif"]}]\'>', (string) $input->getControl());

    $input = $form->addUpload('file6')->addRule(Form::Image)->addRule(Form::MimeType, null, ['video/*', 'text/html']);
    Assert::match('<input type="file" name="file6" accept="image/gif, image/png, image/jpeg, image/webp, image/avif, video/*, text/html" id="frm-file6" data-nette-rules=\'[{"op":":fileSize",%a%},{"op":":mimeType","msg":"The uploaded file is not in the expected format.","arg":["image/gif","image/png","image/jpeg","image/webp","image/avif","video/*","text/html"]}]\'>', (string) $input->getControl());

    $input = $form->addUpload('file7')->addRule(Form::Image)->addRule(Form::MimeType, null, ['image/gif', 'text/html']);
    Assert::match('<input type="file" name="file7" accept="image/gif, image/png, image/jpeg, image/webp, image/avif, text/html" id="frm-file7" data-nette-rules=\'[{"op":":fileSize",%a%},{"op":":mimeType","msg":"The uploaded file is not in the expected format.","arg":["image/gif","image/png","image/jpeg","image/webp","image/avif","text/html"]}]\'>', (string) $input->getControl());

    $input = $form->addUpload('file8')->addRule(Form::MimeType, null, ['image/gif', 'text/html'])->addRule(Form::Image);
    Assert::match('<input type="file" name="file8" accept="image/gif, text/html, image/png, image/jpeg, image/webp, image/avif" id="frm-file8" data-nette-rules=\'[{"op":":fileSize",%a%},{"op":":mimeType","msg":"The uploaded file is not in the expected format.","arg":["image/gif","text/html","image/png","image/jpeg","image/webp","image/avif"]}]\'>', (string) $input->getControl());

    $input = $form->addUpload('file9')->addRule(Form::MimeType, null, 'video/*')->addRule(Form::MimeType, null, 'text/html');
    Assert::match('<input type="file" name="file9" accept="video/*, text/html" id="frm-file9" data-nette-rules=\'[{"op":":fileSize",%a%},{"op":":mimeType","msg":"The uploaded file is not in the expected format.","arg":["video/*","text/html"]}]\'>', (string) $input->getControl());

    $input = $form->addUpload('file10')->addRule(Form::MimeType, null, 'video/*')->addRule(Form::Image)->addRule(Form::MimeType, null, 'text/html');
    Assert::match('<input type="file" name="file10" accept="video/*, image/gif, image/png, image/jpeg, image/webp, image/avif, text/html" id="frm-file10" data-nette-rules=\'[{"op":":fileSize",%a%},{"op":":mimeType","msg":"The uploaded file is not in the expected format.","arg":["video/*","image/gif","image/png","image/jpeg","image/webp","image/avif","text/html"]}]\'>', (string) $input->getControl());
});


test('container', function () {
	$form = new Form;
	$container = $form->addContainer('container');
	$input = $container->addUpload('file');

	Assert::match('<input type="file" name="container[file]" id="frm-container-file" data-nette-rules=%a%>', (string) $input->getControl());
});


test('rendering options', function () {
	$form = new Form;
	$input = $form->addUpload('file');

	Assert::same('file', $input->getOption('type'));

	Assert::null($input->getOption('rendered'));
	$input->getControl();
	Assert::true($input->getOption('rendered'));
});
