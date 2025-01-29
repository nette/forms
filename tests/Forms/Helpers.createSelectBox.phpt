<?php

/**
 * Test: Nette\Forms\Helpers::createSelectBox()
 */

declare(strict_types=1);

use Nette\Forms\Helpers;
use Nette\Utils\Html;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('creating select boxes with different structures and attributes', function () {
	Assert::type(
		Html::class,
		Helpers::createSelectBox([]),
	);

	Assert::same(
		'<select></select>',
		(string) Helpers::createSelectBox([]),
	);

	Assert::same(
		'<select><option value="0">a</option></select>',
		(string) Helpers::createSelectBox(['a']),
	);

	Assert::same(
		'<select><option value="a">First</option><option value="b">Second</option></select>',
		(string) Helpers::createSelectBox(
			['a' => 'First', 'b' => 'Second'],
		),
	);

	Assert::same(
		'<select><option value="a">First</option><optgroup label="Group"><option value="0">A</option><option value="1">B</option></optgroup></select>',
		(string) Helpers::createSelectBox(
			[
				'a' => 'First',
				'Group' => ['A', 'B'],
			],
		),
	);

	Assert::same(
		'<select><option id="item-a" value="a">Hello</option><optgroup label="Group"><option id="item-b" value="0">World</option></optgroup></select>',
		(string) Helpers::createSelectBox(
			[
				'a' => Html::el('', 'Hello')->id('item-a'),
				'Group' => [Html::el('', 'World')->id('item-b')],
			],
		),
	);

	Assert::same(
		'<select><option title="Hello" style="color:blue" a="b" value="a" selected>First</option><option title="Hello" style="color:blue" a="b" value="b" disabled>Second</option></select>',
		(string) Helpers::createSelectBox(
			['a' => 'First', 'b' => 'Second'],
			[
				'disabled:' => ['b' => true],
				'selected?' => ['a'],
				'title' => 'Hello',
				'style' => ['color' => 'blue'],
				'a' => 'b',
			],
		),
	);

	Assert::same(
		'<select><option disabled value="a">First</option><option disabled value="b" selected>Second</option></select>',
		(string) Helpers::createSelectBox(
			['a' => 'First', 'b' => 'Second'],
			['disabled:' => true, 'selected?' => 'b'],
		),
	);

	Assert::same(
		'<select><option value="a">First</option><option value="b" selected>Second</option></select>',
		(string) Helpers::createSelectBox(
			['a' => 'First', 'b' => 'Second'],
			[],
			'b',
		),
	);

	Assert::same(
		'<select><optgroup label="0"><option value="a" selected>First</option></optgroup><optgroup label="1"><option value="a">First</option></optgroup></select>',
		(string) Helpers::createSelectBox(
			[['a' => 'First'], ['a' => 'First']],
			[],
			'a',
		),
	);

	Assert::same(
		'<select><option value="a" selected>First</option><option value="b" selected>Second</option></select>',
		(string) Helpers::createSelectBox(
			['a' => 'First', 'b' => 'Second'],
			[],
			['a', 'b'],
		),
	);

	Assert::same(
		'<select><optgroup label="0"><option value="a" selected>First</option></optgroup><optgroup label="1"><option value="a" selected>First</option></optgroup></select>',
		(string) Helpers::createSelectBox(
			[['a' => 'First'], ['a' => 'First']],
			[],
			['a', 'b'],
		),
	);
});
