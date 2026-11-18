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


function conditionMet(Form $form, Nette\Forms\Control $control, string $op, mixed $arg): bool
{
	$text = $form->addText('t' . count($form->getComponents()));
	$text->addConditionOn($control, $op, $arg)
		->addRule(Form::Filled, 'met');
	$text->validate();
	return $text->getErrors() === ['met'];
}


test('empty date equals nothing', function () {
	$form = new Form;
	$date = $form->addDate('date');

	Assert::false(conditionMet($form, $date, Form::Equal, '2026-01-01'));
	Assert::false(conditionMet($form, $date, Form::Equal, ['2026-01-01', '2026-01-02']));
	Assert::true(conditionMet($form, $date, Form::NotEqual, '2026-01-01'));
	Assert::true(conditionMet($form, $date, ~Form::Equal, '2026-01-01'));
	Assert::false(conditionMet($form, $date, ~Form::NotEqual, '2026-01-01'));
});


test('null or empty expected value matches no date', function () {
	$form = new Form;
	$empty = $form->addDate('empty');
	$filled = $form->addDate('filled')->setValue('2026-01-01');

	Assert::false(conditionMet($form, $empty, Form::Equal, ''));
	Assert::false(conditionMet($form, $empty, Form::Equal, [null]));
	Assert::false(conditionMet($form, $filled, Form::Equal, ''));
	Assert::false(conditionMet($form, $filled, Form::Equal, [null]));
	Assert::true(conditionMet($form, $filled, Form::Equal, [null, '2026-01-01']));
});


test('equal on a filled date', function () {
	$form = new Form;
	$date = $form->addDate('date')->setValue('2026-01-01');

	Assert::true(conditionMet($form, $date, Form::Equal, '2026-01-01'));
	Assert::false(conditionMet($form, $date, Form::Equal, '2026-01-02'));
	Assert::false(conditionMet($form, $date, ~Form::Equal, '2026-01-01'));
	Assert::true(conditionMet($form, $date, Form::NotEqual, '2026-01-02'));
});


test('equal on a time with and without seconds', function () {
	$form = new Form;
	$minutes = $form->addTime('minutes')->setValue('10:30:15');
	Assert::true(conditionMet($form, $minutes, Form::Equal, '10:30:45'));
	Assert::false(conditionMet($form, $minutes, Form::Equal, '10:31'));

	$seconds = $form->addTime('seconds', withSeconds: true)->setValue('10:30:15');
	Assert::true(conditionMet($form, $seconds, Form::Equal, '10:30:15'));
	Assert::false(conditionMet($form, $seconds, Form::Equal, '10:30:45'));

	$empty = $form->addTime('empty', withSeconds: true);
	Assert::false(conditionMet($form, $empty, Form::Equal, '10:30:15'));
	Assert::true(conditionMet($form, $empty, Form::NotEqual, '10:30:15'));
});


test('equal on a datetime-local', function () {
	$form = new Form;
	$dt = $form->addDateTime('dt')->setValue('2026-01-01 10:30');
	Assert::true(conditionMet($form, $dt, Form::Equal, new DateTimeImmutable('2026-01-01 10:30:59')));
	Assert::false(conditionMet($form, $dt, Form::Equal, '2026-01-02 10:30'));

	$empty = $form->addDateTime('empty');
	Assert::false(conditionMet($form, $empty, Form::Equal, '2026-01-01 10:30'));
	Assert::true(conditionMet($form, $empty, ~Form::Equal, '2026-01-01 10:30'));
});


test('equality between two controls with empty values', function () {
	$form = new Form;
	$a = $form->addDate('a');
	$b = $form->addDate('b');
	Assert::false(conditionMet($form, $a, Form::Equal, $b));
	Assert::true(conditionMet($form, $a, Form::NotEqual, $b));

	$b->setValue('2026-01-01');
	Assert::false(conditionMet($form, $a, Form::Equal, $b));
	Assert::false(conditionMet($form, $b, Form::Equal, $a));

	$a->setValue('2026-01-01');
	Assert::true(conditionMet($form, $a, Form::Equal, $b));
});


test('empty value is in no range, an empty bound is open', function () {
	$form = new Form;
	$empty = $form->addDate('empty');
	Assert::false(conditionMet($form, $empty, Form::Range, ['2026-01-01', '2026-12-31']));
	Assert::false(conditionMet($form, $empty, Form::Range, [null, '2026-12-31']));
	Assert::true(conditionMet($form, $empty, ~Form::Range, ['2026-01-01', '2026-12-31']));

	$date = $form->addDate('date')->setValue('2026-06-01');
	Assert::true(conditionMet($form, $date, Form::Range, ['', '2026-12-31']));
	Assert::true(conditionMet($form, $date, Form::Range, ['2026-01-01', '']));
	Assert::false(conditionMet($form, $date, Form::Range, ['2026-07-01', null]));

	$time = $form->addTime('time')->setValue('23:00');
	Assert::true(conditionMet($form, $time, Form::Range, ['22:00', '02:00']));
	Assert::true(conditionMet($form, $time, Form::Range, ['22:00', null]));
	Assert::false(conditionMet($form, $time, Form::Range, [null, '22:00']));
});


test('equal is exported in the format of the control', function () {
	$form = new Form;
	$date = $form->addDate('date');
	$date->addRule(Form::Equal, 'msg', new DateTimeImmutable('2026-07-11 15:00'));
	Assert::same(
		[['op' => ':equal', 'msg' => 'msg', 'arg' => ['2026-07-11']]],
		ClientRuleExporter::export($date->getRules()),
	);
});
