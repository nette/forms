<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\SubmissionSource prototype - pluggable sources of submitted data.
 */

use Nette\Forms\{Form, SubmissionSource};
use Nette\Forms\Sources\{HttpSource, NullSource};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_GET = $_POST = $_FILES = $_COOKIE = [];
});


test('NullSource: anchored but never submitted, defaults readable', function () {
	$form = (new Form)->setSubmissionSource(new NullSource);
	$form->addText('name')->setDefaultValue('John');

	Assert::true($form->isAnchored());
	Assert::false($form->isSubmitted());
	Assert::false($form->isSuccess());
	Assert::same(['name' => 'John'], $form->getValues('array'));
});


test('custom source feeds submitted data to controls', function () {
	$form = new Form;
	$form->setSubmissionSource(new class implements SubmissionSource {
		public function receiveData(Form $form): ?array
		{
			return ['name' => 'injected'];
		}


		public function prepare(Form $form): void
		{
		}
	});
	$form->addText('name');

	Assert::true($form->isSubmitted());
	Assert::same(['name' => 'injected'], $form->getValues('array'));
});


test('HttpSource: same-origin POST is accepted', function () {
	$request = new Nette\Http\Request(
		new Nette\Http\UrlScript,
		post: ['name' => 'John'],
		method: 'POST',
		headers: ['Sec-Fetch-Site' => 'same-origin'],
	);
	$form = (new Form)->setSubmissionSource(new HttpSource($request));
	$form->addText('name');

	Assert::true($form->isSuccess());
	Assert::same(['name' => 'John'], $form->getValues('array'));
});


test('HttpSource: cross-origin POST is rejected unless allowed', function () {
	$request = new Nette\Http\Request(
		new Nette\Http\UrlScript,
		post: ['name' => 'John'],
		method: 'POST',
		headers: ['Sec-Fetch-Site' => 'cross-site'],
	);
	$form = (new Form)->setSubmissionSource(new HttpSource($request));
	$form->addText('name');
	Assert::false($form->isSubmitted());

	$source = new HttpSource($request);
	$source->allowCrossOrigin();
	$form = (new Form)->setSubmissionSource($source);
	$form->addText('name');
	Assert::true($form->isSubmitted());
});


test('late source lets already attached controls load their values (descendant without default source)', function () {
	$request = new Nette\Http\Request(
		new Nette\Http\UrlScript,
		post: ['name' => 'John'],
		method: 'POST',
		headers: ['Sec-Fetch-Site' => 'same-origin'],
	);
	$form = new class extends Form {
		protected function createDefaultSource(): ?SubmissionSource
		{
			return null;
		}
	};
	$form->addText('name');
	$form->setSubmissionSource(new HttpSource($request));

	Assert::true($form->isSuccess());
	Assert::same(['name' => 'John'], $form->getValues('array'));
});


test('late source with nested repeaters survives removal of surplus default items', function () {
	$request = new Nette\Http\Request(
		new Nette\Http\UrlScript,
		post: ['outer' => [['name' => 'John', 'phones' => [['number' => '123']]]]],
		method: 'POST',
		headers: ['Sec-Fetch-Site' => 'same-origin'],
	);
	$form = new class extends Form {
		protected function createDefaultSource(): ?SubmissionSource
		{
			return null;
		}
	};
	// default: 2 creates two items up front; the submission has only one,
	// so loading drops the second item incl. its inner repeater
	$form->addRepeater('outer', function ($container) {
		$container->addText('name');
		$container->addRepeater('phones', function ($inner) {
			$inner->addText('number');
		});
	}, default: 2);
	$form->setSubmissionSource(new HttpSource($request));

	Assert::true($form->isSuccess());
	Assert::same(
		['outer' => [['name' => 'John', 'phones' => [['number' => '123']]]]],
		$form->getValues('array'),
	);
});


test('late source materializes repeater items, like when the form is anchored to a presenter', function () {
	$request = new Nette\Http\Request(
		new Nette\Http\UrlScript,
		post: ['persons' => [['name' => 'John'], ['name' => 'Jane']]],
		method: 'POST',
		headers: ['Sec-Fetch-Site' => 'same-origin'],
	);
	$form = new class extends Form {
		protected function createDefaultSource(): ?SubmissionSource
		{
			return null;
		}
	};
	$form->addRepeater('persons', function ($container) {
		$container->addText('name');
	});
	$form->setSubmissionSource(new HttpSource($request));

	Assert::true($form->isSuccess());
	Assert::same(['persons' => [['name' => 'John'], ['name' => 'Jane']]], $form->getValues('array'));
});


test('HttpSource installs the tracker of a named form before rendering', function () {
	$form = new Nette\Forms\Form('myForm');
	Assert::null($form->getComponent(HttpSource::TrackerId, throw: false));

	$form->fireRenderEvents();
	$tracker = $form->getComponent(HttpSource::TrackerId, throw: false);
	Assert::type(Nette\Forms\Controls\HiddenField::class, $tracker);
	Assert::same('myForm', $tracker->getValue());
});


test('source can be set only once', function () {
	$form = (new Form)->setSubmissionSource(new NullSource);
	Assert::exception(
		fn() => $form->setSubmissionSource(new NullSource),
		Nette\InvalidStateException::class,
		'Submission source has already been set.',
	);
});


test('HttpSource: request method must match the form method', function () {
	$request = new Nette\Http\Request(
		new Nette\Http\UrlScript,
		post: ['name' => 'John'],
		method: 'POST',
		headers: ['Sec-Fetch-Site' => 'same-origin'],
	);
	$form = (new Form)->setSubmissionSource(new HttpSource($request));
	$form->setMethod($form::Get);
	$form->addText('name');

	Assert::false($form->isSubmitted());
});
