<?php declare(strict_types=1);

/**
 * Test: getValues() mapping to data objects of various shapes.
 */

use Nette\Forms\{Blueprint, Form};
use Nette\Utils\ArrayHash;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


enum Color: string
{
	case Red = 'red';
	case Blue = 'blue';
}


final readonly class DefaultsDto
{
	public function __construct(
		public string $name = '',
		public ?int $age = null,
	) {
	}
}


final readonly class RequiredDto
{
	public function __construct(
		public string $name,
		public ?int $age = null,
	) {
	}
}


final readonly class NestedDto
{
	public function __construct(
		public string $name,
		public AddressDto $address,
		public ?Color $color = null,
	) {
	}
}


final readonly class AddressDto
{
	public function __construct(
		public string $city,
		public string $zip = '',
	) {
	}
}


class VariadicDto
{
	public array $rest;


	public function __construct(
		public string $name,
		mixed ...$rest,
	) {
		$this->rest = $rest;
	}
}


class PropertiesDto
{
	public string $name;
	public ?int $age = null;
}


class PrivateDto
{
	public string $name;
	private ?int $age = null;
}


class ProtectedDto
{
	public string $name;
	protected ?int $age = null;
}


class ReadonlyPropertyDto
{
	public string $name;
	public readonly ?int $age;
}


class PrivateSetDto
{
	public string $name;
	public private(set) ?int $age = null;
}


#[AllowDynamicProperties]
class DynamicDto
{
	public string $name;
}


function createForm(): Form
{
	$form = new Form('sign');
	$form->addText('name');
	$form->addInteger('age');
	$form->setValues(['name' => 'John', 'age' => 42]);
	return $form;
}


test('readonly class with promoted properties that all have defaults', function () {
	$form = createForm();
	Assert::equal(new DefaultsDto('John', 42), $form->getValues(DefaultsDto::class));

	$form->setMappedType(DefaultsDto::class);
	Assert::equal(new DefaultsDto('John', 42), $form->getValues());
});


test('readonly class with a required parameter', function () {
	$form = createForm();
	Assert::equal(new RequiredDto('John', 42), $form->getValues(RequiredDto::class));
});


test('a control without a constructor parameter fails', function () {
	$form = createForm();
	$form->addText('extra');
	Assert::exception(
		fn() => $form->getValues(RequiredDto::class),
		Nette\InvalidStateException::class,
		"Cannot map control 'extra' of form 'sign' to RequiredDto, its constructor has no parameter \$extra.",
	);
});


test('a variadic constructor accepts the remaining values', function () {
	$form = createForm();
	$form->addText('extra')->setValue('x');
	$dto = $form->getValues(VariadicDto::class);
	Assert::same('John', $dto->name);
	Assert::same(['age' => 42, 'extra' => 'x'], $dto->rest);
});


test('a required constructor parameter without a control fails', function () {
	$form = new Form('sign');
	$form->addInteger('age');
	Assert::exception(
		fn() => $form->getValues(RequiredDto::class),
		Nette\InvalidStateException::class,
		"Cannot create RequiredDto, required parameter \$name has no value: control 'name' of form 'sign' is missing, omitted or outside the validation scope.",
	);
});


test('an omitted control leaves the default of an optional parameter', function () {
	$form = createForm();
	$form['age']->setDisabled();
	Assert::equal(new RequiredDto('John'), $form->getValues(RequiredDto::class));
});


test('an omitted control of a required parameter fails', function () {
	$form = createForm();
	$form['name']->setOmitted();
	Assert::exception(
		fn() => $form->getValues(RequiredDto::class),
		Nette\InvalidStateException::class,
		"Cannot create RequiredDto, required parameter \$name has no value: %a%",
	);
});


test('nested containers and enums in constructor objects', function () {
	$form = new Form('sign');
	$form->addText('name')->setValue('John');
	$form->addText('color')->setValue('blue');
	$address = $form->addContainer('address');
	$address->addText('city')->setValue('Prague');

	Assert::equal(
		new NestedDto('John', new AddressDto('Prague'), Color::Blue),
		$form->getValues(NestedDto::class),
	);

	$address->addText('extra');
	Assert::exception(
		fn() => $form->getValues(NestedDto::class),
		Nette\InvalidStateException::class,
		"Cannot map control 'address-extra' of form 'sign' to AddressDto, its constructor has no parameter \$extra.",
	);
});


test('a missing control in a nested container is named by its path', function () {
	$form = new Form('sign');
	$form->addText('name');
	$form->addContainer('address')->addText('zip');

	Assert::exception(
		fn() => $form->getValues(NestedDto::class),
		Nette\InvalidStateException::class,
		"Cannot create AddressDto, required parameter \$city has no value: control 'address-city' of form 'sign' is missing, omitted or outside the validation scope.",
	);
});


test('class with public properties and no constructor', function () {
	$form = createForm();
	$dto = $form->getValues(PropertiesDto::class);
	Assert::type(PropertiesDto::class, $dto);
	Assert::same('John', $dto->name);
	Assert::same(42, $dto->age);
});


test('a control without a property fails', function () {
	$form = createForm();
	$form->addText('extra');
	Assert::exception(
		fn() => $form->getValues(PropertiesDto::class),
		Nette\InvalidStateException::class,
		"Cannot map control 'extra' of form 'sign' to PropertiesDto, it has no property \$extra.",
	);
});


test('a property that is not publicly writable fails', function () {
	foreach ([PrivateDto::class, ProtectedDto::class, ReadonlyPropertyDto::class, PrivateSetDto::class] as $class) {
		Assert::exception(
			fn() => createForm()->getValues($class),
			Nette\InvalidStateException::class,
			"Cannot map control 'age' of form 'sign' to $class, its property \$age is not publicly writable.",
		);
	}
});


test('classes allowing dynamic properties accept any control', function () {
	$form = createForm();
	$form->addText('extra')->setValue('x');

	Assert::equal(ArrayHash::from(['name' => 'John', 'age' => 42, 'extra' => 'x']), $form->getValues());
	Assert::equal((object) ['name' => 'John', 'age' => 42, 'extra' => 'x'], $form->getValues(stdClass::class));
	Assert::same(['name' => 'John', 'age' => 42, 'extra' => 'x'], $form->getValues('array'));

	$dto = $form->getValues(DynamicDto::class);
	Assert::same('x', $dto->extra);
});


test('filling an object instance', function () {
	$form = createForm();
	$obj = new PropertiesDto;
	Assert::same($obj, $form->getValues($obj));
	Assert::same('John', $obj->name);
	Assert::same(42, $obj->age);

	// an instance is filled through properties even if its class has a constructor
	$dto = new VariadicDto('Jack');
	$form = new Form;
	$form->addText('name')->setValue('John');
	$form->getValues($dto);
	Assert::same('John', $dto->name);
	$form = createForm();

	Assert::exception(
		fn() => $form->getValues(new PrivateDto),
		Nette\InvalidStateException::class,
		"Cannot map control 'age' of form 'sign' to PrivateDto, its property \$age is not publicly writable.",
	);
});


test('a control outside the validation scope', function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = ['name' => 'John', 'age' => '42', 'send' => ''];

	$form = new Form;
	$form->allowCrossOrigin();
	$form->addText('name');
	$form->addInteger('age');
	$form->addSubmit('send')->setValidationScope([$form['name']]);

	Assert::truthy($form->isSubmitted());
	Assert::equal(new RequiredDto('John'), $form->getValues(RequiredDto::class));

	$form['send']->setValidationScope([$form['age']]);
	Assert::exception(
		fn() => $form->getValues(RequiredDto::class),
		Nette\InvalidStateException::class,
		"Cannot create RequiredDto, required parameter \$name has no value: %a%",
	);
});


test('blueprint data class round-trips with getValues()', function () {
	$form = new Form('roundTrip');
	$form->addText('name')->setRequired();
	$form->addInteger('age');
	$form->addCheckbox('agree');
	$form->addSelect('color', null, ['red' => 'Red', 'blue' => 'Blue']);
	$form->addDate('born');
	$form->addUpload('photo');
	$form->addContainer('address')->addText('city');
	$form->addSubmit('send');

	$code = new Blueprint()->generateDataClass($form, true);
	Assert::contains('final readonly class RoundTripFormData', $code);
	eval($code);

	$multi = new Form;
	$multi->addMultiUpload('files');
	Assert::contains('public array $files', new Blueprint()->generateDataClass($multi, true));

	$form->setValues([
		'name' => 'John',
		'age' => 42,
		'agree' => true,
		'color' => 'red',
		'born' => '2000-01-01',
		'address' => ['city' => 'Prague'],
	]);
	$dto = $form->getValues('RoundTripFormData');
	Assert::type('RoundTripFormData', $dto);
	Assert::same('John', $dto->name);
	Assert::same(42, $dto->age);
	Assert::true($dto->agree);
	Assert::same('red', $dto->color);
	Assert::equal(new DateTimeImmutable('2000-01-01'), $dto->born);
	Assert::type(Nette\Http\FileUpload::class, $dto->photo);
	Assert::type('RoundTripAddressFormData', $dto->address);
	Assert::same('Prague', $dto->address->city);
});
