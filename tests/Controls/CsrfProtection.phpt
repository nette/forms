<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Controls\CsrfProtection.
 */

use Nette\Forms\Controls\{BaseControl, CsrfProtection};
use Nette\Forms\Form;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$_SERVER['REQUEST_METHOD'] = 'POST';

$form = new Form;
$form->allowCrossOrigin();

$input = $form->addProtection('Security token did not match. Possible CSRF attack.');

$form->onSuccess[] = function () {};
$form->fireEvents();

Assert::same(['This field is required.'], $form->getErrors());
Assert::null($input->getOption('rendered'));
Assert::match('<input type="hidden" name="_token_" value="%S%">', (string) $input->getControl());
Assert::true($input->getOption('rendered'));
Assert::same('hidden', $input->getOption('type'));

$input->setValue(null);
Assert::false(CsrfProtection::validateCsrf($input));

$setValue = new ReflectionMethod(BaseControl::class, 'setValue');
$setValue->invoke($input, '12345678901234567890123456789012345678');
Assert::false(CsrfProtection::validateCsrf($input));

$value = $input->getControl()->value;
$setValue->invoke($input, $value);
Assert::true(CsrfProtection::validateCsrf($input));

session_regenerate_id();
$setValue->invoke($input, $value);
Assert::false(CsrfProtection::validateCsrf($input));



// protection is always the first
$form = new Form;
$form->allowCrossOrigin();
$form->addText('text');
$form->addProtection();
Assert::same([
	$form::ProtectorId => $form[$form::ProtectorId],
	'text' => $form['text'],
], $form->getComponents());
