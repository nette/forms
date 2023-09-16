<?php

/**
 * Test: Nette\Forms default rendering GET form.
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Nette\Forms\Form;
$form->setMethod('GET');
$form->setAction('link?a=b&c[]=d&list[0]=1');
$form->addCheckboxList('list')
	->setItems(['First', 'Second']);
$form->addHidden('userid');
$form->addSubmit('submit', 'Send');

$form->fireEvents();

Assert::match('<form action="link" method="get">
	<input type="hidden" name="a" value="b"><input type="hidden" name="c[]" value="d">

<table>
<tr>
	<th><label></label></th>

	<td><label><input type="checkbox" name="list[]" value="0">First</label><br><label><input type="checkbox" name="list[]" value="1">Second</label></td>
</tr>

<tr>
	<th></th>

	<td><input type="submit" name="_submit" value="Send" class="button"></td>
</tr>
</table>

<input type="hidden" name="userid" value="">
</form>', $form->__toString(true));

Assert::same('link?a=b&c[]=d&list[0]=1', $form->getAction());
