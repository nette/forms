<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte;

use Nette;
use Nette\Forms\Form;
use Nette\Utils\Html;
use function end, explode, is_object, parse_url, preg_replace, preg_split, urldecode;
use const PHP_URL_QUERY, PREG_SPLIT_NO_EMPTY;


/**
 * Runtime helpers for Latte v2 & v3.
 * @internal
 */
class Runtime
{
	use Nette\StaticClass;

	public static function initializeForm(Form $form): void
	{
		$form->fireRenderEvents();
		foreach ($form->getControls() as $control) {
			$control->setOption('rendered', false);
		}
	}


	/**
	 * Renders form begin.
	 */
	public static function renderFormBegin(Form $form, array $attrs, bool $withTags = true): string
	{
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

		return $s . ($withTags ? $form->getElementPrototype()->endTag() . "\n" : '');
	}


	public static function item($item, $global): object
	{
		if (is_object($item)) {
			return $item;
		}
		$form = end($global->formsStack) ?: throw new \LogicException('Form declaration is missing, did you use {form} or <form n:name> tag?');
		return $form[$item];
	}
}
