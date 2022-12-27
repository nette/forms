<?php

/** @phpVersion 8.0 */

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
	$res,
);

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
	$res,
);
