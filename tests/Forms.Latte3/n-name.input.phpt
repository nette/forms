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
	'%A%echo ($ʟ_input = $this->global->forms->item(\'foo\'))->getControlPart()->attributes() %A%',
	$latte->compile('<input n:name="foo">'),
);

Assert::match(
	'%A%echo ($ʟ_input = $this->global->forms->item(\'foo\'))->getControlPart(\'\')->attributes() %A%',
	$latte->compile('<input n:name="foo:">'),
);

Assert::exception(
	fn() => $latte->compile('<input n:name="foo: class => foo">'),
	Latte\CompileException::class,
	"Unexpected '=>foo', expecting end of attribute in n:name (on line 1 at column 27)",
);

Assert::match(
	'%A%echo ($ʟ_input = $this->global->forms->item(\'foo\'))->getControlPart(\'x\')->attributes() %A%',
	$latte->compile('<input n:name="foo:x">'),
);

Assert::match(
	'%A%echo ($ʟ_input = $this->global->forms->item(\'foo\'))->getControlPart(\'x\')->attributes() %A%',
	$latte->compile('<input n:name=\'"foo":"x"\'>'),
);
