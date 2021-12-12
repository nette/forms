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
	public static function renderFormPrint(Form $form): void
	{
		$blueprint = new Latte\Runtime\Blueprint;
		$end = $blueprint->printCanvas();
		$blueprint->printHeader('Form ' . $form->getName());
		$blueprint->printCode((new Nette\Forms\Rendering\LatteRenderer)->render($form), 'latte');
		echo $end;
	}


	/**
	 * Generates blueprint of form data class.
	 */
	public static function renderFormClassPrint(Form $form): void
	{
		$blueprint = new Latte\Runtime\Blueprint;
		$end = $blueprint->printCanvas();
		$blueprint->printHeader('Form Data Class ' . $form->getName());
		$generator = new Nette\Forms\Rendering\DataClassGenerator;
		$blueprint->printCode($generator->generateCode($form));
		if (PHP_VERSION_ID >= 80000) {
			$generator->propertyPromotion = true;
			$blueprint->printCode($generator->generateCode($form));
		}

		echo $end;
	}
}
