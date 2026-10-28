<?php declare(strict_types=1);

/**
 * Code that PHPStan must accept without errors.
 */

use Nette\Forms\{Container, Control, Form};
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


// Issue #350: custom validators take the control and the argument, possibly narrowed
function testCustomValidators(Form $form): void
{
	$num = $form->addInteger('num');
	$num->addRule([MyValidators::class, 'validateDivisibility'], 'Must be divisible by %d', 8)
		->addRule(fn(Control $input, mixed $arg): bool => $input->getValue() > $arg, 'Must be greater than %d', 0)
		->addRule(fn(TextInput $input): bool => $input->getValue() !== null)
		->addRule(fn(): bool => true)
		->addCondition([MyValidators::class, 'validateDivisibility'], 2)
			->setRequired()
		->endCondition()
		->addConditionOn($num, fn(BaseControl $input, int $arg): bool => true, 1)
			->setRequired();
}


class MyValidators
{
	public static function validateDivisibility(BaseControl $input, int $arg): bool
	{
		return $input->getValue() % $arg === 0;
	}
}
