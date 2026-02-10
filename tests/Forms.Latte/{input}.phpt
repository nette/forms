<?php

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new FormsExtension);

Assert::match(
	'%A%echo $this->global->forms->item(\'foo\')->getControl() %A%',
	$latte->compile('{input foo}'),
);

Assert::match(
	'%A%echo $this->global->forms->item(\'foo\')->getControl()->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{input foo class => foo}'),
);

Assert::match(
	'%A%echo $this->global->forms->item(\'foo\')->getControl()->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{input foo, class => foo}'),
);

Assert::match(
	'%A%echo $this->global->forms->item(\'foo\')->getControlPart(\'\') %A%',
	$latte->compile('{input foo:}'),
);

Assert::exception(
	fn() => $latte->compile('{input foo: class => foo}'),
	Latte\CompileException::class,
	"Unexpected '=>', expecting end of tag in {input} (on line 1 at column 19)",
);

Assert::match(
	'%A%echo $this->global->forms->item(\'foo\')->getControlPart(\'\') %A%',
	$latte->compile('{input foo:,}'),
);

Assert::match(
	'%A%echo $this->global->forms->item(\'foo\')->getControlPart(\'\')->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{input foo:, class => foo}'),
);

Assert::match(
	'%A%echo $this->global->forms->item(\'foo\')->getControlPart(\'x\') %A%',
	$latte->compile('{input foo:x}'),
);

Assert::match(
	'%A%echo $this->global->forms->item(\'foo\')->getControlPart(\'x\')->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{input foo:x, class => foo}'),
);

Assert::match(
	'%A%echo $this->global->forms->item(\'foo\')->getControlPart(\'x\') %A%',
	$latte->compile('{input "foo":"x"}'),
);

Assert::match(
	'%A%echo $this->global->forms->item(\'foo\')->getControlPart(\'x\') %A%',
	$latte->compile('{input "foo" : "x"}'),
);
