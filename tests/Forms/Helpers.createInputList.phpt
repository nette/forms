<?php

/**
 * Test: Nette\Forms\Helpers::createInputList()
 */

declare(strict_types=1);

use Nette\Forms\Helpers;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('generating input lists with various configurations', function () {
	Assert::same(
		'',
		Helpers::createInputList([]),
	);

	Assert::same(
		'<label><input value="0">a</label>',
		Helpers::createInputList(['a']),
	);

	Assert::same(
		'<label><input value="0">1</label>',
		Helpers::createInputList([1]),
	);

	Assert::same(
		'<label><input value="a">First</label><label><input value="b">Second</label>',
		Helpers::createInputList(
			['a' => 'First', 'b' => 'Second'],
		),
	);

	Assert::same(
		'<label class="button"><input type="checkbox" value="a">First</label><label class="button"><input type="checkbox" value="b">Second</label>',
		Helpers::createInputList(
			['a' => 'First', 'b' => 'Second'],
			['type' => 'checkbox'],
			['class' => 'button'],
		),
	);

	Assert::same(
		'<label style="color:blue" class="class1 class2"><input title="Hello" type="checkbox" checked value="a">First</label><label style="color:blue"><input title="Hello" type="radio" value="b">Second</label>',
		Helpers::createInputList(
			['a' => 'First', 'b' => 'Second'],
			[
				'type:' => ['a' => 'checkbox', 'b' => 'radio'],
				'checked?' => ['a'],
				'title' => 'Hello',
			],
			[
				'class:' => ['a' => ['class1', 'class2']],
				'style' => ['color' => 'blue'],
			],
		),
	);

	Assert::same(
		'<label><input value="a">First</label><br><label><input value="b">Second</label>',
		Helpers::createInputList(
			['a' => 'First', 'b' => 'Second'],
			null,
			null,
			'<br>',
		),
	);

	Assert::same(
		'<div><label><input value="a">First</label></div><div><label><input value="b">Second</label></div>',
		Helpers::createInputList(
			['a' => 'First', 'b' => 'Second'],
			null,
			null,
			Html::el('div'),
		),
	);

	Assert::same(
		'<label><input value="a">First</label><label><input value="b">Second</label>',
		Helpers::createInputList(
			['a' => 'First', 'b' => 'Second'],
			null,
			null,
			Html::el(null),
		),
	);
});
