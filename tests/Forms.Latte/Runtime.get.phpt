<?php

declare(strict_types=1);

use Nette\Bridges\FormsLatte\Runtime;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Form;
$form->setMethod('GET');
$form->addText('arg1');
$form->addText('arg2');
$form->setAction('http://example.com/?do=foo-submit&arg0=1&arg1=1&arg2[x]=1#toc');

Assert::same(
	'<form action="http://example.com/#toc" method="get">',
	Runtime::renderFormBegin($form, []),
);

Assert::match(
	'<input type="hidden" name="do" value="foo-submit"><input type="hidden" name="arg0" value="1"></form>',
	Runtime::renderFormEnd($form),
);
