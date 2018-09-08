<?php

/**
 * Test: Nette\Forms\Controls\ChoiceControl.
 */

declare(strict_types=1);

use Tester\Assert;


require __DIR__ . '/../bootstrap.php';


class ChoiceControl extends Nette\Forms\Controls\ChoiceControl
{
}


test(function () {
	$choiceCOntrol = new ChoiceControl();
	$choiceCOntrol->setItems(['A', 'B']);

	Assert::same([0 => 'A', 1 => 'B'], $choiceCOntrol->getItems());
});


test(function () {
	$choiceCOntrol = new ChoiceControl();
	$choiceCOntrol->setItems(['A', 'B'], false);

	Assert::same(['A' => 'A', 'B' => 'B'], $choiceCOntrol->getItems());
});


test(function () {
	$choiceCOntrol = new ChoiceControl(null, ['A' => 'A']);
	$choiceCOntrol->prependItem('B');

	Assert::same(['B' => 'B', 'A' => 'A'], $choiceCOntrol->getItems());
});


test(function () {
	$choiceCOntrol = new ChoiceControl(null, ['A' => 'A']);
	$choiceCOntrol->prependItem('B', 'C');

	Assert::same(['C' => 'B', 'A' => 'A'], $choiceCOntrol->getItems());
});


test(function () {
	$choiceCOntrol = new ChoiceControl(null, ['A']);
	$choiceCOntrol->prependItem('B', null, true);

	Assert::same([0 => 'B', 1 => 'A'], $choiceCOntrol->getItems());
});


test(function () {
	$choiceCOntrol = new ChoiceControl(null, ['A' => 'A']);
	$choiceCOntrol->appendItem('B');

	Assert::same(['A' => 'A', 'B' => 'B'], $choiceCOntrol->getItems());
});


test(function () {
	$choiceCOntrol = new ChoiceControl(null, ['A' => 'A']);
	$choiceCOntrol->appendItem('B', 'C');

	Assert::same(['A' => 'A', 'C' => 'B'], $choiceCOntrol->getItems());
});


test(function () {
	$choiceCOntrol = new ChoiceControl(null, ['A']);
	$choiceCOntrol->appendItem('B', null, true);

	Assert::same([0 => 'A', 1 => 'B'], $choiceCOntrol->getItems());
});
