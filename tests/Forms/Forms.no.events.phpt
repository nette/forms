<?php

/**
 * Test: Nette\Forms onSuccess.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


setUp(function () {
	$_SERVER['REQUEST_METHOD'] = 'POST';
	$_POST = ['send' => 'x'];
	$_COOKIE[Nette\Http\Helpers::StrictCookieName] = '1';
	ob_start();
	Form::initialize(true);
});


test('firing events without handlers', function () {
	Assert::error(function () {
		$form = new Form;
		$form->addSubmit('send');
		$form->fireEvents();
	}, E_USER_WARNING);
});

test('no error with onSuccess handler', function () {
	Assert::noError(function () {
		$form = new Form;
		$form->addSubmit('send');
		$form->onSuccess[] = function () {};
		$form->fireEvents();
	});
});

test('no error with onSubmit handler', function () {
	Assert::noError(function () {
		$form = new Form;
		$form->addSubmit('send');
		$form->onSubmit[] = function () {};
		$form->fireEvents();
	});
});

test('no error with button onClick handler', function () {
	Assert::noError(function () {
		$form = new Form;
		$form->addSubmit('send')
			->onClick[] = function () {};
		$form->fireEvents();
	});
});
