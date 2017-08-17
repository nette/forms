<?php

/**
 * Test: Nette\Forms naming container.
 */

declare(strict_types=1);

use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';

$_POST = [
	'name' => 'jim',
	'first' => [
		'name' => 'jim',
		'age' => '40',
		'second' => [
			'name' => 'david',
		],
	],
	'invalid' => true,
];


$first = new Nette\Forms\Container;
$first->addText('name');
$first->addText('age');

$second = $first->addContainer('second');
$second->addText('name');

$first->setDefaults([
	'name' => 'xxx',
	'age' => '50',
	'second' => [
		'name' => 'yyy',
		'age' => '30',
	],
]);

Assert::equal(ArrayHash::from([
	'name' => 'xxx',
	'age' => '50',
	'second' => ArrayHash::from([
		'name' => 'yyy',
	]),
]), $first->getValues());


$form = new Form;
$form->addText('name');
$form['first'] = $first;
$invalid = $form->addContainer('invalid');
$invalid->addText('name');
$form->addSubmit('send');


Assert::truthy($form->isSubmitted());
Assert::equal(ArrayHash::from([
	'name' => 'jim',
	'first' => ArrayHash::from([
		'name' => 'jim',
		'age' => '40',
		'second' => ArrayHash::from([
			'name' => 'david',
		]),
	]),
	'invalid' => ArrayHash::from([
		'name' => '',
	]),
]), $form->getValues());


$form->setValues([
	'name' => 'new1',
	'first' => [
		'name' => 'new2',
	],
]);

Assert::truthy($form->isSubmitted());
Assert::equal(ArrayHash::from([
	'name' => 'new1',
	'first' => ArrayHash::from([
		'name' => 'new2',
		'age' => '40',
		'second' => ArrayHash::from([
			'name' => 'david',
		]),
	]),
	'invalid' => ArrayHash::from([
		'name' => '',
	]),
]), $form->getValues());


$form->setValues([
	'name' => 'new1',
	'first' => [
		'name' => 'new2',
	],
], true);

Assert::truthy($form->isSubmitted());
Assert::equal(ArrayHash::from([
	'name' => 'new1',
	'first' => ArrayHash::from([
		'name' => 'new2',
		'age' => '',
		'second' => ArrayHash::from([
			'name' => '',
		]),
	]),
	'invalid' => ArrayHash::from([
		'name' => '',
	]),
]), $form->getValues());


$form->reset();

Assert::false($form->isSubmitted());
Assert::equal(ArrayHash::from([
	'name' => '',
	'first' => ArrayHash::from([
		'name' => '',
		'age' => '',
		'second' => ArrayHash::from([
			'name' => '',
		]),
	]),
	'invalid' => ArrayHash::from([
		'name' => '',
	]),
]), $form->getValues());


$form->setDefaults([
	'name' => 'new3',
]);

Assert::equal(ArrayHash::from([
	'name' => 'new3',
	'first' => ArrayHash::from([
		'name' => '',
		'age' => '',
		'second' => ArrayHash::from([
			'name' => '',
		]),
	]),
	'invalid' => ArrayHash::from([
		'name' => '',
	]),
]), $form->getValues());
