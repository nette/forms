<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Container::getSubmittedData() & getSubmittedValue() - layered access to submitted data.
 */

use Nette\Forms\{Container, Form};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_GET = $_POST = $_FILES = [];
});


class CompositeDateInput extends Nette\Forms\Controls\BaseControl
{
	public string $day = '';
	public string $month = '';
	public string $year = '';


	public function loadHttpData(): void
	{
		$this->day = $this->getSubmittedValue(Form::DataLine, 'day') ?? '';
		$this->month = $this->getSubmittedValue(Form::DataLine, 'month') ?? '';
		$this->year = $this->getSubmittedValue(Form::DataLine, 'year') ?? '';
	}
}


class LegacyDateInput extends Nette\Forms\Controls\BaseControl
{
	public string $day = '';
	public array $list = [];


	public function loadHttpData(): void
	{
		$this->day = @$this->getHttpData(Form::DataLine, '[day]') ?? ''; // @ - deprecated
		$this->list = @$this->getHttpData(Form::DataLine, '[items][]'); // @ - deprecated
	}
}


test('container receives its slice of submitted data', function () {
	$_POST = [
		'name' => 'jim',
		'person' => ['age' => '30', 'address' => ['city' => 'Praha']],
		'send' => '',
	];

	$form = new Form;
	$form->allowCrossOrigin();
	$person = $form->addContainer('person');
	$address = $person->addContainer('address');

	Assert::same($_POST, $form->getSubmittedData());
	Assert::same(['age' => '30', 'address' => ['city' => 'Praha']], $person->getSubmittedData());
	Assert::same(['city' => 'Praha'], $address->getSubmittedData());
});


test('controls in nested containers load values through their parents', function () {
	$_POST = [
		'person' => ['name' => 'jim', 'address' => ['city' => 'Praha']],
		'send' => '',
	];

	$form = new Form;
	$form->allowCrossOrigin();
	$form->addSubmit('send');
	$person = $form->addContainer('person');
	$person->addText('name');
	$person->addContainer('address')->addText('city');

	Assert::same([
		'person' => ['name' => 'jim', 'address' => ['city' => 'Praha']],
	], $form->getValues('array'));
});


test('unsafe control names are sanitized only at the top level', function () {
	$_POST = [
		'_submit' => 'a',
		'_style' => ['x', 'y'],
		'_123' => 'b',
		'person' => ['submit' => 'c'],
	];

	$form = new Form;
	$form->allowCrossOrigin();
	$form->addText('submit');
	$form->addCheckboxList('style', items: ['x' => 1, 'y' => 2]);
	$form->addText('123');
	$form->addContainer('person')->addText('submit');

	Assert::same('_submit', $form['submit']->getHtmlName());
	Assert::same('_style[]', $form['style']->getHtmlName());
	Assert::same('_123', $form['123']->getHtmlName());
	Assert::same('person[submit]', $form['person']['submit']->getHtmlName());

	Assert::same('a', $form['submit']->getValue());
	Assert::same(['x', 'y'], $form['style']->getValue());
	Assert::same('b', $form['123']->getValue());
	Assert::same('c', $form['person']['submit']->getValue());
});


test('composite control reads sub-values via getSubmittedValue() with key', function () {
	$_POST = [
		'date' => ['day' => '7', 'month' => '12', 'year' => '2025'],
		'person' => ['date' => ['day' => '1', 'month' => '2', 'year' => '2000']],
		'send' => '',
	];

	$form = new Form;
	$form->allowCrossOrigin();
	$form['date'] = new CompositeDateInput;
	$form->addContainer('person')['date'] = new CompositeDateInput;

	Assert::true((bool) $form->isSubmitted());
	Assert::same('7', $form['date']->day);
	Assert::same('12', $form['date']->month);
	Assert::same('1', $form['person']['date']->day);
	Assert::same('2000', $form['person']['date']->year);
});


test('deprecated getHttpData() with htmlTail works through the layered path', function () {
	$_POST = [
		'date' => ['day' => '7', 'items' => ['a', 'b']],
		'person' => ['date' => ['day' => '1', 'items' => []]],
		'send' => '',
	];

	$form = new Form;
	$form->allowCrossOrigin();
	$form['date'] = new LegacyDateInput;
	$form->addContainer('person')['date'] = new LegacyDateInput;

	Assert::true((bool) $form->isSubmitted());
	Assert::same('7', $form['date']->day);
	Assert::same(['a', 'b'], $form['date']->list);
	Assert::same('1', $form['person']['date']->day);
	Assert::same([], $form['person']['date']->list);
});


test('file uploads inside containers', function () {
	$file = new Nette\Http\FileUpload([
		'name' => 'license.txt',
		'type' => '',
		'size' => 3013,
		'tmp_name' => 'tmp',
		'error' => 0,
	]);
	$_POST = ['send' => ''];
	$_FILES = ['person' => [
		'name' => ['avatar' => 'license.txt'],
		'type' => ['avatar' => 'text/plain'],
		'tmp_name' => ['avatar' => 'tmp'],
		'error' => ['avatar' => 0],
		'size' => ['avatar' => 3013],
	]];

	$form = new Form;
	$form->allowCrossOrigin();
	$person = $form->addContainer('person');
	$person->addUpload('avatar');

	Assert::equal($file, $person['avatar']->getValue());
});


test('unsubmitted form returns empty data for containers', function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';

	$form = new Form;
	$person = $form->addContainer('person');
	$person->addText('name');

	Assert::false($form->isSubmitted());
	Assert::same([], $form->getSubmittedData());
	Assert::same([], $person->getSubmittedData());
	Assert::same('', $person['name']->getValue());
});


test('GET form distributes query data through containers', function () {
	$_SERVER['REQUEST_METHOD'] = 'GET';
	$_GET = ['person' => ['name' => 'jim'], 'send' => ''];
	$_SERVER['REQUEST_URI'] = '/?person[name]=jim&send=';

	$form = new Form;
	$form->setMethod($form::Get);
	$form->addContainer('person')->addText('name');
	$form->addSubmit('send');

	Assert::truthy($form->isSubmitted());
	Assert::same(['person' => ['name' => 'jim']], $form->getValues('array'));
});


test('overridden HTML name attribute is an absolute pointer bypassing the hierarchy', function () {
	$_POST = [
		'fixed_field' => 'external',
		'person' => ['a' => 'nested', 'date' => ['day' => '7']],
		'send' => '',
	];

	$form = new Form;
	$form->allowCrossOrigin();
	$person = $form->addContainer('person');
	$input = $person->addText('a');

	Assert::same('nested', $input->getValue());
	$input->setHtmlAttribute('name', 'fixed_field');
	// the override escapes the container and reads the top-level key, exactly as in 3.x
	Assert::same('external', $input->getValue());

	// composite sub-values follow the override too
	$date = new CompositeDateInput;
	$form['mydate'] = $date;
	$date->setHtmlAttribute('name', 'person[date]');
	Assert::same('7', $date->day);
});


testException('detached container has no submitted data', function () {
	$container = new Container;
	$container->getSubmittedData();
}, Nette\InvalidStateException::class, 'Container has no parent.');
