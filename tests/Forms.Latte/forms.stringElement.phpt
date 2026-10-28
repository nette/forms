<?php declare(strict_types=1);

/**
 * Test: control rendering its label and element as strings.
 */

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Utils\Html;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class StringControl extends BaseControl
{
	public function getLabel(string|Stringable|null $caption = null): Html|string|null
	{
		return '<b>Label</b>';
	}


	public function getControl(): Html|string
	{
		return '<i>Control</i>';
	}
}


$form = new Form;
$form['foo'] = new StringControl;

$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new FormsExtension);
$latte->addProvider('uiControl', ['myForm' => $form]);

$render = fn(string $template) => $latte->renderToString('{form myForm}' . $template . '{/form}');

Assert::match(
	'%A%<b>Label</b><i>Control</i>%A%',
	$render('{label foo /}{input foo}'),
);

Assert::exception(
	fn() => $render('{label foo}x{/label}'),
	Nette\InvalidStateException::class,
	"Label of control 'foo' is a string, not an Html element, so it cannot be paired or get attributes.",
);

Assert::exception(
	fn() => $render('{label foo, class => a /}'),
	Nette\InvalidStateException::class,
	"Label of control 'foo' is a string, not an Html element, so it cannot be paired or get attributes.",
);

Assert::exception(
	fn() => $render('{input foo, class => a}'),
	Nette\InvalidStateException::class,
	"Control 'foo' renders a string, not an Html element, so it cannot get attributes.",
);

Assert::exception(
	fn() => $render('<label n:name=foo></label>'),
	Nette\InvalidStateException::class,
	"Label of control 'foo' is a string, not an Html element, so it cannot be used with n:name or rendered by parts.",
);

Assert::exception(
	fn() => $render('<input n:name=foo>'),
	Nette\InvalidStateException::class,
	"Control 'foo' renders a string, not an Html element, so it cannot be used with n:name or rendered by parts.",
);
