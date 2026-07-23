<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Form default submission source - a bare form reads its own HTTP request, like in 3.x.
 */

use Nette\Forms\Form;
use Nette\Forms\Sources\HttpSource;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_GET = $_POST = $_FILES = $_COOKIE = [];
});


test('bare form works standalone', function () {
	$_POST = ['name' => 'John'];

	$form = new Form;
	$form->allowCrossOrigin(); // header spoofing via $_SERVER does not work under the CGI SAPI
	$form->addText('name');

	Assert::true($form->isSuccess());
	Assert::same(['name' => 'John'], $form->getValues('array'));
});


test('named form gets the frm- id and a render-time tracker', function () {
	$form = new Form;
	Assert::null($form->getName());
	Assert::null($form->getComponent(HttpSource::TrackerId, throw: false));

	$named = new Form('alone');
	$named->fireRenderEvents(); // installs the tracker
	Assert::same('alone', $named->getName());
	Assert::same('frm-alone', $named->getElementPrototype()->id);
	$tracker = $named->getComponent(HttpSource::TrackerId, throw: false);
	Assert::type(Nette\Forms\Controls\HiddenField::class, $tracker);
	Assert::same('alone', $tracker->getValue());
});


test('explicit source cannot be set after the default materialized', function () {
	$form = new Form;
	$form->isAnchored(); // materializes the default HttpSource

	Assert::exception(
		fn() => $form->setSubmissionSource(new Nette\Forms\Sources\NullSource),
		Nette\InvalidStateException::class,
		'Submission source has already been set.',
	);
});


test('descendant without a default source stays detached and throws', function () {
	$form = new class extends Form {
		protected function createDefaultSource(): ?Nette\Forms\SubmissionSource
		{
			return null;
		}
	};
	$form->addText('name');

	Assert::false($form->isAnchored());
	Assert::exception(
		fn() => $form->isSubmitted(),
		Nette\InvalidStateException::class,
		'%a%no source of submitted data%a%',
	);
});


test('overriding isAnchored() without a data source throws from receiveHttpData()', function () {
	$form = new class extends Form {
		protected function createDefaultSource(): ?Nette\Forms\SubmissionSource
		{
			return null;
		}


		public function isAnchored(): bool
		{
			return true;
		}
	};

	Assert::exception(
		fn() => $form->isSubmitted(),
		Nette\InvalidStateException::class,
		'%a%must also override receiveHttpData() or set a submission source%a%',
	);
});
