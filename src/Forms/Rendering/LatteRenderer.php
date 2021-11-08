<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Rendering;

use Nette;
use Nette\Forms\Form;


/**
 * Generates Latte blueprint of form.
 */
final class LatteRenderer
{
	public function render(Form $form): string
	{
		$dict = new \SplObjectStorage;
		$dummyForm = new Form;

		foreach ($form->getControls() as $name => $input) {
			$dict[$input] = $dummyInput = new class extends Nette\Forms\Controls\BaseControl {
				public $inner;


				public function getLabel($name = null)
				{
					return $this->inner->getLabel()
						? '{label ' . $this->inner->lookupPath(Form::class) . '/}'
						: null;
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
					return $key === 'rendered'
						? parent::getOption($key)
						: $this->inner->getOption($key, $default);
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
		$dummyForm->onRender = $form->onRender;
		$dummyForm->fireRenderEvents();

		if ($renderer instanceof DefaultFormRenderer) {
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
		return $body;
	}
}
