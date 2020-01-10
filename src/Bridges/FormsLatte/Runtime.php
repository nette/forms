<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte;

use Latte;
use Nette;
use Nette\Forms\Form;
use Nette\Utils\Html;


/**
 * Runtime helpers for Latte.
 * @internal
 */
class Runtime
{
	use Nette\StaticClass;

	/**
	 * Renders form begin.
	 */
	public static function renderFormBegin(Form $form, array $attrs, bool $withTags = true): string
	{
		$form->fireRenderEvents();
		foreach ($form->getControls() as $control) {
			$control->setOption('rendered', false);
		}
		$el = $form->getElementPrototype();
		$el->action = (string) $el->action;
		$el = clone $el;
		if ($form->isMethod('get')) {
			$el->action = preg_replace('~\?[^#]*~', '', $el->action, 1);
		}
		$el->addAttributes($attrs);
		return $withTags ? $el->startTag() : $el->attributes();
	}


	/**
	 * Renders form end.
	 */
	public static function renderFormEnd(Form $form, bool $withTags = true): string
	{
		$s = '';
		if ($form->isMethod('get')) {
			foreach (preg_split('#[;&]#', (string) parse_url($form->getElementPrototype()->action, PHP_URL_QUERY), -1, PREG_SPLIT_NO_EMPTY) as $param) {
				$parts = explode('=', $param, 2);
				$name = urldecode($parts[0]);
				$prefix = explode('[', $name, 2)[0];
				if (!isset($form[$prefix])) {
					$s .= Html::el('input', ['type' => 'hidden', 'name' => $name, 'value' => urldecode($parts[1])]);
				}
			}
		}

		foreach ($form->getControls() as $control) {
			if ($control->getOption('type') === 'hidden' && !$control->getOption('rendered')) {
				$s .= $control->getControl();
			}
		}

		if (iterator_count($form->getComponents(true, Nette\Forms\Controls\TextInput::class)) < 2) {
			$s .= "<!--[if IE]><input type=IEbug disabled style=\"display:none\"><![endif]-->\n";
		}

		return $s . ($withTags ? $form->getElementPrototype()->endTag() . "\n" : '');
	}


	/**
	 * Generates blueprint of form.
	 */
	public static function renderBlueprint($form): void
	{
		$dummyForm = new Form;
		$dict = new \SplObjectStorage;
		foreach ($form->getControls() as $name => $input) {
			$dict[$input] = $dummyInput = new class extends Nette\Forms\Controls\BaseControl {
				public $inner;


				public function getLabel($name = null)
				{
					return $this->inner->getLabel() ? '{label ' . $this->inner->lookupPath(Form::class) . '}' : null;
				}


				public function getControl()
				{
					return '{input ' . $this->inner->lookupPath(Form::class) . '}';
				}


				public function isRequired(): bool
				{
					return $this->inner->isRequired();
				}


				public function getOption($key, $default = null)
				{
					return $key === 'rendered' ? parent::getOption($key) : $this->inner->getOption($key, $default);
				}
			};
			$dummyInput->inner = $input;
			$dummyForm->addComponent($dummyInput, (string) $dict->count());
			$dummyInput->addError('{inputError ' . $input->lookupPath(Form::class) . '}');
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

		if ($renderer instanceof Nette\Forms\Rendering\DefaultFormRenderer) {
			$renderer->wrappers['error']['container'] = $renderer->getWrapper('error container')->setAttribute('n:ifcontent', true);
			$renderer->wrappers['error']['item'] = $renderer->getWrapper('error item')->setAttribute('n:foreach', '$form->getOwnErrors() as $error');
			$renderer->wrappers['control']['errorcontainer'] = $renderer->getWrapper('control errorcontainer')->setAttribute('n:ifcontent', true);
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

		$blueprint = new Latte\Runtime\Blueprint;
		$end = $blueprint->printCanvas();
		$blueprint->printHeader('Form ' . $form->getName());
		$blueprint->printCode($body);
		echo $end;
	}
}
