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
	'%A%echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getLabel()) %A%',
	$latte->compile('{label foo /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getLabel())?->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{label foo class => foo /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getLabel())?->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{label foo, class => foo /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getLabelPart(\'\')) %A%',
	$latte->compile('{label foo: /}'),
);

Assert::exception(
	fn() => $latte->compile('{label foo: class => foo /}'),
	Latte\CompileException::class,
	"Unexpected '=>', expecting end of tag in {label} (on line 1 at column 19)",
);

Assert::match(
	'%A%echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getLabelPart(\'\')) %A%',
	$latte->compile('{label foo:, /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getLabelPart(\'\'))?->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{label foo:, class => foo /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getLabelPart(\'x\')) %A%',
	$latte->compile('{label foo:x /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getLabelPart(\'x\'))?->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{label foo:x, class => foo /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getLabelPart(\'x\')) %A%',
	$latte->compile('{label "foo":"x" /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item(\'foo\', $this->global)->getLabelPart(\'x\')) %A%',
	$latte->compile('{label "foo" : "x" /}'),
);
