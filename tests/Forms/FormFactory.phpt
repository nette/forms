<?php

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\FormFactory;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$request = (new Nette\Http\RequestFactory)->fromGlobals();
$factory = new FormFactory($request);
$form = $factory->createForm();
Assert::type(Form::class, $form);


$form = $factory->createForm('foo');
Assert::same('foo', $form->getName());
