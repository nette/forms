<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form('signForm');
$form->addText('name')->setRequired();

ob_start();
Nette\Bridges\FormsLatte\Runtime::renderFormClassPrint($form);
$res = ob_get_clean();

Assert::match(
	'%A%class SignFormData
{
	use \Nette\SmartObject;

	public string $name;
}
%A%',
	$res
);

if (PHP_VERSION_ID >= 80000) {
	Assert::match(
		'%A%class SignFormData
{
	use \Nette\SmartObject;

	public function __construct(
		public string $name,
	) {
	}
}
%A%',
		$res
	);
}
