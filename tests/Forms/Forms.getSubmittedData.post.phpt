<?php declare(strict_types=1);

/**
 * Test: Nette\Forms HTTP data.
 */

use Nette\Forms\{Form, Validator};
use Nette\Forms\Sources\HttpSource;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_GET = $_POST = $_FILES = [];
});


test('GET method in POST context', function () {
	$form = new Form;
	$form->setMethod($form::Get);
	$form->addSubmit('send', 'Send');

	Assert::false($form->isSubmitted());
	Assert::false($form->isSuccess());
	Assert::same([], $form->getSubmittedData());
	Assert::same([], $form->getValues('array'));
});


test('tracker ID in POST submission', function () {
	$name = 'name';
	$_POST = [HttpSource::TrackerId => $name];

	$form = new Form($name);
	$form->allowCrossOrigin();
	$form->addSubmit('send', 'Send');

	Assert::truthy($form->isSubmitted());
	Assert::same([HttpSource::TrackerId => $name], $form->getSubmittedData());
	Assert::same([], $form->getValues('array'));
	$form->fireRenderEvents(); // installs the tracker
	Assert::same($name, $form[HttpSource::TrackerId]->getValue());
});


test('submit button not pressed', function () {
	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addSubmit('send', 'Send');
	Assert::false($input->isSubmittedBy());
	Assert::false(Validator::validateSubmitted($input));
});


test('successful POST submission', function () {
	$_POST = ['send' => ''];
	$form = new Form;
	$form->allowCrossOrigin();
	$input = $form->addSubmit('send', 'Send');
	Assert::true($input->isSubmittedBy());
	Assert::true(Validator::validateSubmitted($input));
});
