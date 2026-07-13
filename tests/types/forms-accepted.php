<?php declare(strict_types=1);

/**
 * Code that PHPStan must accept without errors.
 */

use Nette\Forms\Container;
use Nette\Forms\Controls\{BaseControl, TextInput};


// Issue #355: extension methods take arguments
function testContainerExtensionMethodWithArguments(): void
{
	Container::extensionMethod(
		'addZip',
		fn(Container $container, string $name, ?string $label = null): TextInput => $container->addText($name, $label),
	);
	Container::extensionMethod('addVariadic', fn(Container $container, string ...$names) => null);
}


function testControlExtensionMethodWithArguments(): void
{
	BaseControl::extensionMethod('setHint', fn(BaseControl $control, string $hint) => $control->setOption('hint', $hint));
}
