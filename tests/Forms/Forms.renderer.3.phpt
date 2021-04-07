<?php

/**
 * Test: Nette\Forms default rendering with IE fix.
 */

declare(strict_types=1);

use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';
$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';

$form = new Nette\Forms\Form;
$form->addHidden('userid');
$form->addSubmit('submit', 'Send');

$form->onSuccess[] = function () {};
$form->fireEvents();

Assert::match('<form action="" method="post">

<table>
<tr>
	<th></th>

	<td><input type="submit" name="_submit" value="Send" class="button"></td>
</tr>
</table>

<input type="hidden" name="userid" value=""><!--[if IE]><input type=IEbug disabled style="display:none"><![endif]-->
</form>', $form->__toString(true));
