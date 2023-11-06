<?php

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new FormsExtension);

Assert::match(
	'%A%echo ($ʟ_label = $this->global->forms->item(\'foo\')->getLabel()) %A%',
	$latte->compile('{label foo /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = $this->global->forms->item(\'foo\')->getLabel())?->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{label foo class => foo /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = $this->global->forms->item(\'foo\')->getLabel())?->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{label foo, class => foo /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = $this->global->forms->item(\'foo\')->getLabelPart(\'\')) %A%',
	$latte->compile('{label foo: /}'),
);

Assert::exception(
	fn() => $latte->compile('{label foo: class => foo /}'),
	Latte\CompileException::class,
	"Unexpected '=>', expecting end of tag in {label} (on line 1 at column 19)",
);

Assert::match(
	'%A%echo ($ʟ_label = $this->global->forms->item(\'foo\')->getLabelPart(\'\')) %A%',
	$latte->compile('{label foo:, /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = $this->global->forms->item(\'foo\')->getLabelPart(\'\'))?->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{label foo:, class => foo /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = $this->global->forms->item(\'foo\')->getLabelPart(\'x\')) %A%',
	$latte->compile('{label foo:x /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = $this->global->forms->item(\'foo\')->getLabelPart(\'x\'))?->addAttributes([\'class\' => \'foo\']) %A%',
	$latte->compile('{label foo:x, class => foo /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = $this->global->forms->item(\'foo\')->getLabelPart(\'x\')) %A%',
	$latte->compile('{label "foo":"x" /}'),
);

Assert::match(
	'%A%echo ($ʟ_label = $this->global->forms->item(\'foo\')->getLabelPart(\'x\')) %A%',
	$latte->compile('{label "foo" : "x" /}'),
);
