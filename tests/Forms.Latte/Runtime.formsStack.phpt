<?php declare(strict_types=1);

// BC: the removed formsStack provider is emulated as a live alias of the
// internal stack, so legacy generated code using end($global->formsStack) works.

use Nette\Bridges\FormsLatte\Runtime;
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$runtime = new Runtime;
$global = new stdClass;

$form = new Form;
$container = $form->addContainer('person');

$runtime->begin($form, global: $global);
Assert::same($form, end($global->formsStack));

$runtime->begin($container);
Assert::same($container, end($global->formsStack));

$runtime->end();
Assert::same($form, end($global->formsStack));

$runtime->end();
Assert::same(false, end($global->formsStack));
