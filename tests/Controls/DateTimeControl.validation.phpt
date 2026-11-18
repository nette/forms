<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Controls\DateTimeControl validation.
 */

use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


function conditionMet(Form $form, Nette\Forms\Control $control, string $op, mixed $arg): bool
{
	$text = $form->addText('t' . count($form->getComponents()));
	$text->addConditionOn($control, $op, $arg)
		->addRule(Form::Filled, 'met');
	$text->validate();
	return $text->getErrors() === ['met'];
}


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
