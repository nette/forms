<?php

/**
 * Test: Nette\Forms default rendering GET form.
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Nette\Forms\Form;
$form->setMethod('GET');
$form->setAction('link?a=b&c[]=d');
$form->addHidden('userid');
$form->addSubmit('submit', 'Send');

$form->fireEvents();

Assert::match('<form action="link" method="get">
	<input type="hidden" name="a" value="b"><input type="hidden" name="c[]" value="d">

<table>
<tr>
	<th></th>

	<td><input type="submit" name="_submit" value="Send" class="button"></td>
</tr>
</table>

<input type="hidden" name="userid" value=""><!--[if IE]><input type=IEbug disabled style="display:none"><![endif]-->
</form>', $form->__toString(TRUE));

Assert::same('link?a=b&c[]=d', $form->getAction());
