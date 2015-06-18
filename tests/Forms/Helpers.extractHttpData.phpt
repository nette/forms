<?php

/**
 * Test: Nette\Forms\Helpers::extractHttpData()
 */

use Nette\Forms\Form;
use Nette\Forms\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test(function () { // non-multiple
	Assert::same('jim', Helpers::extractHttpData(['name' => 'jim'], 'name', Form::DATA_LINE));
	Assert::same('jim', Helpers::extractHttpData(['name' => 'jim'], 'name', Form::DATA_TEXT));

	Assert::same('jim', Helpers::extractHttpData([
		'first' => ['name' => 'jim'],
	], 'first[name]', Form::DATA_LINE));

	Assert::same('0', Helpers::extractHttpData(['zero' => '0'], 'zero', Form::DATA_LINE));
	Assert::same('', Helpers::extractHttpData(['empty' => ''], 'empty', Form::DATA_LINE));

	Assert::null(Helpers::extractHttpData([], 'missing', Form::DATA_LINE));
	Assert::null(Helpers::extractHttpData(['invalid' => '1'], 'invalid[name]', Form::DATA_LINE));
	Assert::null(Helpers::extractHttpData(['invalid' => ['']], 'invalid', Form::DATA_LINE));
	Assert::null(Helpers::extractHttpData(['invalid' => ['']], 'invalid', Form::DATA_TEXT));

	Assert::same('a  b   c', Helpers::extractHttpData(['text' => "  a\r b \n c "], 'text', Form::DATA_LINE));
	Assert::same("  a\n b \n c ", Helpers::extractHttpData(['text' => "  a\r b \n c "], 'text', Form::DATA_TEXT));
});


test(function () { // multiple
	Assert::same(['1', '2'], Helpers::extractHttpData(['multi' => ['1', '2']], 'multi[]', Form::DATA_LINE));
	Assert::same(['1', '2'], Helpers::extractHttpData(['multi' => ['1', '2']], 'multi[]', Form::DATA_TEXT));
	Assert::same(['1', '2'], Helpers::extractHttpData(['multi' => ['x' => '1', 2 => '2']], 'multi[]', Form::DATA_TEXT));
	Assert::same(['x' => '1', 2 => '2'], Helpers::extractHttpData(['multi' => ['x' => '1', 2 => '2']], 'multi[]', Form::DATA_KEYS | Form::DATA_TEXT));

	Assert::same(['3', '4'], Helpers::extractHttpData([
		'container' => ['image' => ['3', '4']],
	], 'container[image][]', Form::DATA_LINE));

	Assert::same(['0'], Helpers::extractHttpData(['zero' => [0]], 'zero[]', Form::DATA_LINE));
	Assert::same([''], Helpers::extractHttpData(['empty' => ['']], 'empty[]', Form::DATA_LINE));

	Assert::same([], Helpers::extractHttpData([], 'missing[]', Form::DATA_LINE));
	Assert::same([], Helpers::extractHttpData(['invalid' => 'red-dwarf'], 'invalid[]', Form::DATA_LINE));
	Assert::same([], Helpers::extractHttpData(['invalid' => [['']]], 'invalid[]', Form::DATA_LINE));

	Assert::same(['a  b   c'], Helpers::extractHttpData(['text' => ["  a\r b \n c "]], 'text[]', Form::DATA_LINE));
	Assert::same(["  a\n b \n c "], Helpers::extractHttpData(['text' => ["  a\r b \n c "]], 'text[]', Form::DATA_TEXT));
});


test(function () { // files
	$file = new Nette\Http\FileUpload([
		'name' => 'license.txt',
		'type' => NULL,
		'size' => 3013,
		'tmpName' => 'tmp',
		'error' => 0,
	]);

	Assert::equal($file, Helpers::extractHttpData(['avatar' => $file], 'avatar', Form::DATA_FILE));

	Assert::null(Helpers::extractHttpData([], 'missing', Form::DATA_FILE));
	Assert::null(Helpers::extractHttpData(['invalid' => NULL], 'invalid', Form::DATA_FILE));
	Assert::null(Helpers::extractHttpData(['invalid' => [NULL]], 'invalid', Form::DATA_FILE));
	Assert::null(Helpers::extractHttpData([
		'multiple' => ['avatar' => [$file, $file]],
	], 'multiple[avatar]', Form::DATA_FILE));


	Assert::equal([$file, $file], Helpers::extractHttpData([
		'multiple' => ['avatar' => ['x' => $file, NULL, $file]],
	], 'multiple[avatar][]', Form::DATA_FILE));

	Assert::equal(['x' => $file, $file], Helpers::extractHttpData([
		'multiple' => ['avatar' => ['x' => $file, $file]],
	], 'multiple[avatar][]', Form::DATA_KEYS | Form::DATA_FILE));

	Assert::same([], Helpers::extractHttpData([], 'missing[]', Form::DATA_FILE));
	Assert::same([], Helpers::extractHttpData(['invalid' => NULL], 'invalid[]', Form::DATA_FILE));
	Assert::same([], Helpers::extractHttpData(['invalid' => $file], 'invalid[]', Form::DATA_FILE));
	Assert::same([], Helpers::extractHttpData(['invalid' => [NULL]], 'invalid[]', Form::DATA_FILE));
});
