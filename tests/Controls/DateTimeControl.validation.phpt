<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Controls\DateTimeControl validation.
 */

use Nette\Forms\{ClientRuleExporter, Form};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('equal compares dates with the precision of the control', function () {
	$form = new Form;
	$date = $form->addDate('date')->setValue('2026-07-11');
	$date->addRule(Form::Equal, null, new DateTimeImmutable('2026-07-11 15:00')); // time part is ignored
	$date->addRule(Form::Equal, null, '2026-07-11');
	$date->validate();
	Assert::same([], $date->getErrors());

	$other = $form->addDate('other')->setValue('2026-07-11');
	$other->addRule(Form::Equal, null, new DateTimeImmutable('2026-07-12'));
	$other->validate();
	Assert::count(1, $other->getErrors());
});


test('equal compares times by the time of day', function () {
	$form = new Form;
	$time = $form->addTime('time')->setValue('10:30');
	$time->addRule(Form::Equal, null, '10:30');
	$time->addRule(Form::Equal, null, new DateTimeImmutable('2000-01-01 10:30'));
	$time->validate();
	Assert::same([], $time->getErrors());

	$wrong = $form->addTime('wrong')->setValue('10:30');
	$wrong->addRule(Form::Equal, null, ['11:00', '12:00']);
	$wrong->validate();
	Assert::count(1, $wrong->getErrors());
});


test('equal to another control and not equal', function () {
	$form = new Form;
	$from = $form->addDate('from')->setValue('2026-07-11');
	$to = $form->addDate('to')->setValue('2026-07-11');
	$to->addRule(Form::Equal, null, $from);
	$to->validate();
	Assert::same([], $to->getErrors());

	$to->addRule(Form::NotEqual, 'must differ', $from);
	$to->validate();
	Assert::same(['must differ'], $to->getErrors());
});


test('equal is exported in the format of the control', function () {
	$form = new Form;
	$date = $form->addDate('date');
	$date->addRule(Form::Equal, 'msg', new DateTimeImmutable('2026-07-11 15:00'));
	Assert::same(
		[['op' => ':equal', 'msg' => 'msg', 'arg' => '2026-07-11']],
		ClientRuleExporter::export($date->getRules()),
	);
});
