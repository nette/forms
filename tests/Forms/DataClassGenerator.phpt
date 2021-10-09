<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form('signForm');
$form->addText('name')->setRequired();
$form->addInteger('age');
$form->addContainer('cont')
	->addText('name');
$form->addHidden('id');
$form->addCheckbox('agree');
$form->addSubmit('submit', 'Send');

$generator = new Nette\Forms\Rendering\DataClassGenerator;
$res = $generator->generateCode($form);

Assert::match(
	'class SignFormData
{
	use \Nette\SmartObject;

	public string $name;
	public ?int $age;
	public SignContFormData $cont;
	public ?string $id;
	public bool $agree;
}

class SignContFormData
{
	use \Nette\SmartObject;

	public ?string $name;
}
',
	$res
);

$generator->propertyPromotion = true;
$generator->useSmartObject = false;
$res = $generator->generateCode($form);

Assert::match(
	'class SignFormData
{
	public function __construct(
		public string $name,
		public ?int $age,
		public SignContFormData $cont,
		public ?string $id,
		public bool $agree,
	) {
	}
}

class SignContFormData
{
	public function __construct(
		public ?string $name,
	) {
	}
}
',
	$res
);
