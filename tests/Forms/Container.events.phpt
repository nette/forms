<?php declare(strict_types=1);

/**
 * Test: Nette\Forms custom events invoked as methods.
 */

use Nette\Forms\{Container, Form};
use Nette\Forms\Controls\TextInput;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class MyForm extends Form
{
	public array $onFoo = [];
}

class MyContainer extends Container
{
	public array $onFoo = [];
}

class MyInput extends TextInput
{
	public array $onFoo = [];
}


test('custom events of form, container and control can be invoked as methods', function () {
	foreach ([new MyForm, new MyContainer, new MyInput] as $object) {
		$called = [];
		$object->onFoo[] = function (...$args) use (&$called) {
			$called[] = $args;
		};
		$object->onFoo(1, 2);
		Assert::same([[1, 2]], $called);

		Assert::exception(
			fn() => $object->undefined(),
			Nette\MemberAccessException::class,
			'Call to undefined method %a%::undefined().',
		);
	}
});


test('built-in events can be invoked as methods', function () {
	$called = false;
	$form = new Form;
	$form->onRender[] = function () use (&$called) {
		$called = true;
	};
	$form->onRender();
	Assert::true($called);
});
