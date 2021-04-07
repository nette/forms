<?php

/**
 * Test: Nette\Forms onSuccess.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


before(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = ['send' => 'x'];
	$_COOKIE[Nette\Http\Helpers::STRICT_COOKIE_NAME] = '1';
	Form::initialize(true);
});


test('', function () {
	Assert::error(function () {
		$form = new Form;
		$form->addSubmit('send');
		$form->fireEvents();
	}, E_USER_WARNING);
});

test('', function () {
	Assert::noError(function () {
		$form = new Form;
		$form->addSubmit('send');
		$form->onSuccess[] = function () {};
		$form->fireEvents();
	});
});

test('', function () {
	Assert::noError(function () {
		$form = new Form;
		$form->addSubmit('send');
		$form->onSubmit[] = function () {};
		$form->fireEvents();
	});
});

test('', function () {
	Assert::noError(function () {
		$form = new Form;
		$form->addSubmit('send')
			->onClick[] = function () {};
		$form->fireEvents();
	});
});
