<?php

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new FormsExtension);

Assert::match(
	'%A%echo Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getControl() %A%',
	$latte->compile('{input foo}'),
);

Assert::match(
	'%A%echo Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getControl()->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{input foo class => foo}'),
);

Assert::match(
	'%A%echo Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getControl()->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{input foo, class => foo}'),
);

Assert::match(
	'%A%echo Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getControlPart(\'\') %A%',
	$latte->compile('{input foo:}'),
);

Assert::exception(
	fn() => $latte->compile('{input foo: class => foo}'),
	Latte\CompileException::class,
	"Unexpected '=>', expecting end of tag in {input} (on line 1 at column 19)",
);

Assert::match(
	'%A%echo Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getControlPart(\'\') %A%',
	$latte->compile('{input foo:,}'),
);

Assert::match(
	'%A%echo Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getControlPart(\'\')->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{input foo:, class => foo}'),
);

Assert::match(
	'%A%echo Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getControlPart(\'x\') %A%',
	$latte->compile('{input foo:x}'),
);

Assert::match(
	'%A%echo Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getControlPart(\'x\')->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{input foo:x, class => foo}'),
);

Assert::match(
	'%A%echo Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getControlPart(\'x\') %A%',
	$latte->compile('{input "foo":"x"}'),
);

Assert::match(
	'%A%echo Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getControlPart(\'x\') %A%',
	$latte->compile('{input "foo" : "x"}'),
);
