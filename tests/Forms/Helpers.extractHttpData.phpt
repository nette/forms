<?php

/**
 * Test: Nette\Forms\Helpers::extractHttpData()
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Forms\Helpers;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


test('non-multiple', function () {
	Assert::same('jim', Helpers::extractHttpData(['name' => 'jim'], 'name', Form::DataLine));
	Assert::same('jim', Helpers::extractHttpData(['name' => 'jim'], 'name', Form::DataText));

	Assert::same('jim', Helpers::extractHttpData([
		'first' => ['name' => 'jim'],
	], 'first[name]', Form::DataLine));

	Assert::same('0', Helpers::extractHttpData(['zero' => '0'], 'zero', Form::DataLine));
	Assert::same('', Helpers::extractHttpData(['empty' => ''], 'empty', Form::DataLine));

	Assert::null(Helpers::extractHttpData([], 'missing', Form::DataLine));
	Assert::null(Helpers::extractHttpData(['invalid' => '1'], 'invalid[name]', Form::DataLine));
	Assert::null(Helpers::extractHttpData(['invalid' => ['']], 'invalid', Form::DataLine));
	Assert::null(Helpers::extractHttpData(['invalid' => ['']], 'invalid', Form::DataText));

	Assert::same('a  b   c', Helpers::extractHttpData(['text' => "  a\r b \n c "], 'text', Form::DataLine));
	Assert::same("  a\n b \n c ", Helpers::extractHttpData(['text' => "  a\r b \n c "], 'text', Form::DataText));
});


test('multiple', function () {
	Assert::same(['1', '2'], Helpers::extractHttpData(['multi' => ['1', '2']], 'multi[]', Form::DataLine));
	Assert::same(['1', '2'], Helpers::extractHttpData(['multi' => ['1', '2']], 'multi[]', Form::DataText));
	Assert::same(['1', '2'], Helpers::extractHttpData(['multi' => ['x' => '1', 2 => '2']], 'multi[]', Form::DataText));
	Assert::same(['x' => '1', 2 => '2'], Helpers::extractHttpData(['multi' => ['x' => '1', 2 => '2']], 'multi[]', Form::DataKeys | Form::DataText));

	Assert::same(['3', '4'], Helpers::extractHttpData([
		'container' => ['image' => ['3', '4']],
	], 'container[image][]', Form::DataLine));

	Assert::same(['0'], Helpers::extractHttpData(['zero' => [0]], 'zero[]', Form::DataLine));
	Assert::same([''], Helpers::extractHttpData(['empty' => ['']], 'empty[]', Form::DataLine));

	Assert::same([], Helpers::extractHttpData([], 'missing[]', Form::DataLine));
	Assert::same([], Helpers::extractHttpData(['invalid' => 'red-dwarf'], 'invalid[]', Form::DataLine));
	Assert::same([], Helpers::extractHttpData(['invalid' => [['']]], 'invalid[]', Form::DataLine));

	Assert::same(['a  b   c'], Helpers::extractHttpData(['text' => ["  a\r b \n c "]], 'text[]', Form::DataLine));
	Assert::same(["  a\n b \n c "], Helpers::extractHttpData(['text' => ["  a\r b \n c "]], 'text[]', Form::DataText));
});


test('files', function () {
	$file = new Nette\Http\FileUpload([
		'name' => 'license.txt',
		'type' => null,
		'size' => 3013,
		'tmpName' => 'tmp',
		'error' => 0,
	]);

	Assert::equal($file, Helpers::extractHttpData(['avatar' => $file], 'avatar', Form::DataFile));

	Assert::null(Helpers::extractHttpData([], 'missing', Form::DataFile));
	Assert::null(Helpers::extractHttpData(['invalid' => null], 'invalid', Form::DataFile));
	Assert::null(Helpers::extractHttpData(['invalid' => [null]], 'invalid', Form::DataFile));
	Assert::null(Helpers::extractHttpData([
		'multiple' => ['avatar' => [$file, $file]],
	], 'multiple[avatar]', Form::DataFile));

	Assert::equal([$file, $file], Helpers::extractHttpData([
		'multiple' => ['avatar' => ['x' => $file, null, $file]],
	], 'multiple[avatar][]', Form::DataFile));

	Assert::equal(['x' => $file, $file], Helpers::extractHttpData([
		'multiple' => ['avatar' => ['x' => $file, $file]],
	], 'multiple[avatar][]', Form::DataKeys | Form::DataFile));

	Assert::same([], Helpers::extractHttpData([], 'missing[]', Form::DataFile));
	Assert::same([], Helpers::extractHttpData(['invalid' => null], 'invalid[]', Form::DataFile));
	Assert::same([], Helpers::extractHttpData(['invalid' => $file], 'invalid[]', Form::DataFile));
	Assert::same([], Helpers::extractHttpData(['invalid' => [null]], 'invalid[]', Form::DataFile));
});
