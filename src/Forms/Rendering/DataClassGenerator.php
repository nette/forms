<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Rendering;

use Nette\Forms\Container;
use Nette\Forms\Controls;
use Nette\Forms\Form;


/**
 * Generates blueprint of form data class.
 */
final class DataClassGenerator
{
	/** @var string */
	public $classNameSuffix = 'FormData';

	/** @var bool */
	public $propertyPromotion = false;

	/** @var bool */
	public $useSmartObject = true;


	public function generateCode(Form $form, string $baseName = null): string
	{
		$baseName = $baseName ?? preg_replace('~Form$~', '', ucwords((string) $form->getName()));
		return $this->processContainer($form, $baseName);
	}


	private function processContainer(Container $container, string $baseName): string
	{
		$nextCode = '';
		$props = [];
		foreach ($container->getComponents() as $name => $input) {
			if ($input instanceof Controls\BaseControl && $input->isOmitted()) {
				continue;
			} elseif ($input instanceof Controls\Checkbox) {
				$type = 'bool';
			} elseif ($input instanceof Controls\MultiChoiceControl) {
				$type = 'array';
			} elseif ($input instanceof Controls\ChoiceControl) {
				$type = 'string|int';
				if (!$input->isRequired()) {
					$type .= '|null';
				}
			} elseif ($input instanceof Controls\HiddenField || $input instanceof Controls\TextBase) {
				$type = 'string';
				foreach ($input->getRules() as $rule) {
					if ($rule->validator === Form::INTEGER) {
						$type = 'int';
						break;
					}
				}

				if (!$input->isRequired()) {
					$type = '?' . $type;
				}
			} elseif ($input instanceof Controls\UploadControl) {
				$type = '\Nette\Http\FileUpload';
				if (!$input->isRequired()) {
					$type = '?' . $type;
				}
			} elseif ($input instanceof Container) {
				$type = $baseName . ucwords($name);
				$nextCode .= $this->processContainer($input, $type);
				$type .= $this->classNameSuffix;
			} else {
				$type = '';
			}

			$props[] = 'public ' . ($type ? $type . ' ' : '') . '$' . $name;
		}

		$class = $baseName . $this->classNameSuffix;
		return "class $class\n"
			. "{\n"
			. ($this->useSmartObject ? "\tuse \\Nette\\SmartObject;\n\n" : '')
			. ($this->propertyPromotion
				? "\tpublic function __construct(\n"
					. ($props ? "\t\t" . implode(",\n\t\t", $props) . ",\n" : '')
					. "\t) {\n\t}\n"
				: ($props ? "\t" . implode(";\n\t", $props) . ";\n" : '')
			)
			. "}\n\n"
			. $nextCode;
	}
}
