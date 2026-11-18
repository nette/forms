<?php declare(strict_types=1);

/**
 * Test: Nette\Forms\Validation\Message
 */

use Nette\Forms\Validation\Message;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('plain template without parameters', function () {
	$message = new Message('forms.email', 'Please enter a valid email address.');
	Assert::same('forms.email', $message->id);
	Assert::same('Please enter a valid email address.', (string) $message);
});


test('string cast returns the template with unsubstituted placeholders', function () {
	$message = new Message('forms.mimeType', 'The uploaded file is in an unsupported format %type.', ['type' => 'text/html']);
	Assert::same('The uploaded file is in an unsupported format %type.', (string) $message);
});


test('named parameters are substituted into the template', function () {
	$message = new Message('forms.mimeType', 'The uploaded file is in an unsupported format %type.', ['type' => 'text/html']);
	Assert::same('The uploaded file is in an unsupported format text/html.', $message->substitute($message->template));
	Assert::same('Nepodporovaný formát text/html.', $message->substitute('Nepodporovaný formát %type.'));
});


test('longer parameter names win over their prefixes', function () {
	$message = new Message('test', '%minLength/%min', ['min' => 1, 'minLength' => 20]);
	Assert::same('20/1', $message->substitute($message->template));
});


test('%label and %value are left for the message renderer', function () {
	$message = new Message('test', '%label: %value must be at least %min', ['min' => 18]);
	Assert::same('%label: %value must be at least 18', $message->substitute($message->template));
});


test('non-stringable parameters are skipped', function () {
	$message = new Message('test', 'allowed %allowed', ['allowed' => ['a', 'b']]);
	Assert::same('allowed %allowed', $message->substitute($message->template));
	Assert::same('allowed a, b', $message->substitute($message->template, fn($v) => implode(', ', $v)));
});


test('count is exposed as %count parameter', function () {
	$message = new Message('forms.length.min', 'Please enter at least %count characters.', count: 3);
	Assert::same(3, $message->count);
	Assert::same('Please enter at least 3 characters.', $message->substitute($message->template));
});


test('withTemplate() replaces the template and keeps everything else', function () {
	$message = new Message('forms.length.min', 'Please enter at least %count characters.', ['foo' => 'bar'], count: 3);
	$custom = $message->withTemplate('Zadejte alespoň %count znaky.');
	Assert::same('forms.length.min', $custom->id);
	Assert::same('Zadejte alespoň %count znaky.', (string) $custom);
	Assert::same(['foo' => 'bar'], $custom->parameters);
	Assert::same(3, $custom->count);
	Assert::notSame($message, $custom);
});
