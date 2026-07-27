<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette\ComponentModel\IComponent;
use Nette\Utils\Html;


/**
 * Generates blueprints for forms.
 */
final class Blueprint
{
	private const ClassNameSuffix = 'FormData';


	/**
	 * Generates blueprint of form Latte template.
	 */
	public static function latte(Form $form, bool $exit = true): void
	{
		$blueprint = new self;
		$blueprint->printBegin();
		$blueprint->printHeader('Form ' . $form->getName());
		$blueprint->printCode($blueprint->generateLatte($form), 'latte');
		$blueprint->printEnd();
		if ($exit) {
			exit;
		}
	}


	/**
	 * Generates blueprint of form data class.
	 */
	public static function dataClass(Form $form, bool $exit = true): void
	{
		$blueprint = new self;
		$blueprint->printBegin();
		$blueprint->printHeader('Form Data Class ' . $form->getName());
		$blueprint->printCode($blueprint->generateDataClass($form), 'php');
		$blueprint->printCode($blueprint->generateDataClass($form, true), 'php');
		$blueprint->printEnd();
		if ($exit) {
			exit;
		}
	}


	private function printBegin(): void
	{
		echo '<script src="https://nette.github.io/resources/prism/prism.js"></script>';
		echo '<link rel="stylesheet" href="https://nette.github.io/resources/prism/prism.css">';
		echo "<div style='all:initial; position:fixed; overflow:auto; z-index:1000; left:0; right:0; top:0; bottom:0; color:black; background:white; padding:1em'>\n";
	}


	private function printEnd(): void
	{
		echo "</div>\n";
	}


	private function printHeader(string $string): void
	{
		echo "<h1 style='all:initial; display:block; font-size:2em; margin:1em 0'>",
			htmlspecialchars($string),
			"</h1>\n";
	}


	private function printCode(string $code, string $lang): void
	{
		echo '<pre><code class="language-', htmlspecialchars($lang), '">',
			htmlspecialchars($code),
			"</code></pre>\n";
	}


	public function generateLatte(Form $form): string
	{
		$dict = new \SplObjectStorage;
		$dummyForm = (new Form)->setSubmissionSource(new Sources\NullSource); // never submitted, dummy controls never load HTTP data

		foreach ($form->getComponentTree() as $input) {
			if ($input instanceof Repeater && !$this->findRepeater($input)) {
				// topmost repeater renders as a single {repeater} block
				$dict[$input] = $dummyInput = new class extends Controls\BaseControl {
					public string $block = '';


					public function getLabel(string|\Stringable|null $caption = null): Html|string|null
					{
						return null;
					}


					public function getControl(): Html|string
					{
						return $this->block;
					}
				};
				$dummyInput->block = $this->generateRepeaterLatte($input, $input->lookupPath(Form::class));
				$dummyForm->addComponent($dummyInput, (string) $dict->count());
				continue;

			} elseif (!$input instanceof Control || $this->findRepeater($input)) {
				if ($input instanceof Control) {
					$dict[$input] = null; // controls inside repeaters are covered by the block
				}
				continue;
			}

			$dict[$input] = $dummyInput = new class extends Controls\BaseControl {
				public Control $inner;


				public function getLabel(string|\Stringable|null $caption = null): Html|string|null
				{
					return $this->inner->getLabel()
						? '{label ' . Helpers::lookupPathToForm($this->inner) . '/}'
						: null;
				}


				public function getControl(): Html|string
				{
					return '{input ' . Helpers::lookupPathToForm($this->inner) . '}';
				}


				public function isRequired(): bool
				{
					return $this->inner->getRules()->isRequired();
				}


				public function getOption(mixed $key): mixed
				{
					return $key === 'rendered'
						? parent::getOption($key)
						: $this->inner->getOption($key);
				}
			};
			$dummyInput->inner = $input;
			$dummyForm->addComponent($dummyInput, (string) $dict->count());
			$dummyInput->addError('{inputError ' . Helpers::lookupPathToForm($input) . '}');
		}

		foreach ($form->getGroups() as $group) {
			$dummyGroup = $dummyForm->addGroup();
			foreach ($group->getOptions() as $k => $v) {
				$dummyGroup->setOption($k, $v);
			}

			foreach ($group->getControls() as $control) {
				if ($dict[$control]) {
					$dummyGroup->add($dict[$control]);
				}
			}
		}

		$renderer = clone $form->getRenderer();
		$dummyForm->setRenderer($renderer);
		$dummyForm->onRender = $form->onRender;
		$dummyForm->fireRenderEvents();

		if ($renderer instanceof Rendering\DefaultFormRenderer) {
			$renderer->wrappers['error']['container'] = $renderer->getWrapper('error container')->setAttribute('n:ifcontent', true);
			$renderer->wrappers['error']['item'] = $renderer->getWrapper('error item')
				->setAttribute('n:foreach', '$form->getOwnErrors() as $error');
			$renderer->wrappers['control']['errorcontainer'] = $renderer->getWrapper('control errorcontainer')
				->setAttribute('n:ifcontent', true);
			$dummyForm->addError('{$error}');

			ob_start();
			$dummyForm->render('end');
			$end = ob_get_clean();
		}

		ob_start();
		$dummyForm->render();
		$body = ob_get_clean();

		$body = str_replace($dummyForm->getElementPrototype()->startTag(), '<form n:name="' . $form->getName() . '">', $body);
		$body = str_replace($end ?? '', '</form>', $body);
		return $body;
	}


	/**
	 * Returns the outermost repeater the component belongs to.
	 */
	private function findRepeater(IComponent $component): ?Repeater
	{
		$res = null;
		$parent = $component->getParent();
		while ($parent instanceof Container) {
			if ($parent instanceof Repeater) {
				$res = $parent;
			}
			$parent = $parent->getParent();
		}

		return $res;
	}


	private function generateRepeaterLatte(Repeater $repeater, string $name, int $depth = 0): string
	{
		$components = $repeater->getComponents();
		$item = reset($components) ?: $repeater->createTemplate();
		assert($item instanceof Container);

		$pad = str_repeat("\t", $depth);
		return $pad . '{repeater ' . $name . "}\n"
			. $this->generateRepeaterItem($item, '', $depth + 1)
			. $pad . "\t<button n:repeater-remove>" . $repeater->getRemoveButtonPrototype()->getText() . "</button>\n"
			. $pad . "{/repeater}\n"
			. $pad . '<button n:repeater-add="' . $name . '">' . $repeater->getAddButtonPrototype()->getText() . "</button>\n";
	}


	private function generateRepeaterItem(Container $container, string $prefix, int $depth): string
	{
		$pad = str_repeat("\t", $depth);
		$s = '';
		foreach ($container->getComponents() as $name => $child) {
			if ($child instanceof Repeater) {
				$s .= $this->generateRepeaterLatte($child, $prefix . $name, $depth);
			} elseif ($child instanceof Container) {
				$s .= $this->generateRepeaterItem($child, "$prefix$name-", $depth);
			} elseif ($child instanceof Control) {
				$label = $child->getLabel() ? '{label ' . $prefix . $name . ' /} ' : '';
				$s .= $pad . $label . '{input ' . $prefix . $name . "}\n";
			}
		}

		return $s;
	}


	public function generateDataClass(
		Container $container,
		?bool $propertyPromotion = false,
		?string $baseName = null,
	): string
	{
		$baseName ??= preg_replace('~Form$~', '', ucwords((string) $container->getName()));
		$nextCode = '';
		$props = [];
		foreach ($container->getComponents() as $name => $input) {
			if ($input instanceof Control && $input->isOmitted()) {
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
					if ($rule->validator === Form::Integer) {
						$type = 'int';
						break;
					}
				}

				if (!$input->isRequired() && $input->isNullable()) {
					$type = '?' . $type;
				}
			} elseif ($input instanceof Controls\UploadControl) {
				$type = '\Nette\Http\FileUpload';
				if (!$input->isRequired()) {
					$type = '?' . $type;
				}
			} elseif ($input instanceof Repeater) {
				$itemType = $baseName . ucwords((string) $name);
				$nextCode .= $this->generateDataClass($input->createTemplate(), $propertyPromotion, $itemType);
				$type = 'array';
			} elseif ($input instanceof Container) {
				$type = $baseName . ucwords((string) $name);
				$nextCode .= $this->generateDataClass($input, $propertyPromotion, $type);
				$type .= self::ClassNameSuffix;
			} else {
				$type = '';
			}

			$props[] = 'public ' . ($type ? $type . ' ' : '') . '$' . $name;
		}

		$class = $baseName . self::ClassNameSuffix;
		return "class $class\n"
			. "{\n"
			. ($propertyPromotion
				? "\tpublic function __construct(\n"
					. ($props ? "\t\t" . implode(",\n\t\t", $props) . ",\n" : '')
					. "\t) {\n\t}\n"
				: ($props ? "\t" . implode(";\n\t", $props) . ";\n" : '')
			)
			. "}\n\n"
			. $nextCode;
	}
}
