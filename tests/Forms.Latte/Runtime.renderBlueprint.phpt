<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form('signForm');
$form->addGroup('Personal data');
$form->addText('name')->setRequired('Enter your name');
$form->addSubmit('submit', 'Send');

ob_start();
Nette\Bridges\FormsLatte\Runtime::renderBlueprint($form);
$res = ob_get_clean();

Assert::match(
	'%A%<form n:name="signForm">

<ul class="error" n:ifcontent>
	<li n:foreach="$form->getOwnErrors() as $error">{$error}</li>
</ul>


<fieldset>
<legend>Personal data</legend>

<table>
<tr class="required">
	<th>{label name/}</th>

	<td>{input name}
	<span class="error" n:ifcontent>{inputError name}</span></td>
</tr>

<tr>
	<th></th>

	<td>{input submit}</td>
</tr>
</table>
</fieldset>

</form>%A%',
	html_entity_decode($res)
);
