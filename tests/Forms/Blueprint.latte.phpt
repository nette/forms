<?php

declare(strict_types=1);

use Nette\Forms\Blueprint;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$form = new Form('signForm');
$form->addGroup('Personal data');
$form->addText('name')->setRequired('Enter your name');
$form->addContainer('cont')
	->addText('name');
$form->addHidden('id');
$form->addCheckbox('agree');
$form->addGroup();
$form->addSubmit('submit', 'Send');

$form->onRender[] = function ($form) {
	$renderer = $form->getRenderer();
	$renderer->wrappers['form']['container'] = Html::el('div')->id('form');
	$renderer->wrappers['group']['container'] = null;
	$renderer->wrappers['group']['label'] = 'h3';
	$renderer->wrappers['pair']['container'] = null;
	$renderer->wrappers['controls']['container'] = 'dl';
	$renderer->wrappers['control']['container'] = 'dd';
	$renderer->wrappers['control']['.odd'] = 'odd';
	$renderer->wrappers['label']['container'] = 'dt';
	$renderer->wrappers['label']['suffix'] = ':';
};

$res = (new Blueprint)->generateLatte($form);

Assert::match(
	'<form n:name="signForm">

<ul class="error" n:ifcontent>
	<li n:foreach="$form->getOwnErrors() as $error">{$error}</li>
</ul>

<div id="form">

<h3>Personal data</h3>

<dl>

	<dt>{label name/}:</dt>

	<dd>{input name}
	<span class="error" n:ifcontent>{inputError name}</span></dd>



	<dt>{label cont-name/}:</dt>

	<dd class="odd">{input cont-name}
	<span class="error" n:ifcontent>{inputError cont-name}</span></dd>



	<dt></dt>

	<dd>{input agree}
	<span class="error" n:ifcontent>{inputError agree}</span></dd>

</dl>



<dl>

	<dt></dt>

	<dd>{input submit}</dd>

</dl>

</div>
</form>',
	$res,
);
