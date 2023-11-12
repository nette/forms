<?php

/** @phpVersion 8.0 */

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new FormsExtension);

Assert::exception(
	fn() => $latte->compile('<form n:form></form>'),
	Latte\CompileException::class,
	'Did you mean <form n:name=...> ? (on line 1 at column 7)',
);

Assert::exception(
	fn() => $latte->compile('<form n:name></form>'),
	Latte\CompileException::class,
	'Missing arguments in n:name (on line 1 at column 7)',
);

Assert::exception(
	fn() => $latte->compile('<form n:inner-name></form>'),
	Latte\CompileException::class,
	'Unexpected attribute n:inner-name, did you mean n:inner-label? (on line 1 at column 7)',
);


Assert::exception(
	fn() => $latte->compile('<html>{form /}'),
	Latte\CompileException::class,
	'Missing arguments in {form} (on line 1 at column 7)',
);

Assert::exception(
	fn() => $latte->compile('<html>{formContainer /}'),
	Latte\CompileException::class,
	'Missing arguments in {formContainer} (on line 1 at column 7)',
);


Assert::exception(
	fn() => $latte->compile('<html>{label /}'),
	Latte\CompileException::class,
	'Missing arguments in {label} (on line 1 at column 7)',
);

Assert::exception(
	fn() => $latte->compile('<html>{input /}'),
	Latte\CompileException::class,
	'Missing arguments in {input} (on line 1 at column 7)',
);

Assert::exception(
	fn() => $latte->compile('<html>{name /}'),
	Latte\CompileException::class,
	'Unexpected tag {name} (on line 1 at column 7)',
);
