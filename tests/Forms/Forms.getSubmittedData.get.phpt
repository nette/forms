<?php declare(strict_types=1);

/**
 * Test: Nette\Forms HTTP data.
 */

use Nette\Forms\Form;
use Nette\Forms\Sources\HttpSource;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_GET = $_POST = $_FILES = [];
});


test('empty GET form submission', function () {
	$form = new Form;
	$form->setMethod($form::Get);
	$form->addSubmit('send', 'Send');

	Assert::false($form->isSubmitted());
	Assert::false($form->isSuccess());
	Assert::same([], $form->getSubmittedData());
	Assert::same([], $form->getValues('array'));
});


test('unsubmitted form handling', function () {
	$form = new Form;
	$form->allowCrossOrigin();
	$form->addSubmit('send', 'Send');

	Assert::false($form->isSubmitted());
	Assert::same([], $form->getSubmittedData());
	Assert::same([], $form->getValues('array'));
});


test('tracker ID in GET submission', function () {
	$name = 'name';
	$_GET = [HttpSource::TrackerId => $name];
	$_SERVER['REQUEST_URI'] = '/?' . http_build_query($_GET);

	$form = new Form($name);
	$form->setMethod($form::Get);
	$form->addSubmit('send', 'Send');

	Assert::truthy($form->isSubmitted());
	Assert::same([HttpSource::TrackerId => $name], $form->getSubmittedData());
	Assert::same([], $form->getValues('array'));
	$form->fireRenderEvents(); // installs the tracker
	Assert::same($name, $form[HttpSource::TrackerId]->getValue());
});
