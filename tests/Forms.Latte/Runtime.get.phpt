<?php

/**
 * Test: Nette\Bridges\FormsLatte\FormMacros: GET form
 */

use Nette\Bridges\FormsLatte\Runtime;
use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

$form = new Form;
$form->setMethod('GET');
$form->action = 'http://example.com/?do=foo-submit#toc';

Assert::same(
	'<form action="http://example.com/#toc" method="get">',
	Runtime::renderFormBegin($form, array())
);

Assert::match(
	'<input type="hidden" name="do" value="foo-submit"><!--[if IE]><input type=IEbug disabled style="display:none"><![endif]-->
</form>',
	Runtime::renderFormEnd($form)
);
