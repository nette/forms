<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte;

use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Utils\Html;


/**
 * Runtime helpers for Latte v3.
 * @internal
 */
class Runtime
{
	/** @var Container[] */
	private array $stack = [];


	/**
	 * Renders form begin.
	 */
	public function renderFormBegin(array $attrs, bool $withTags = true): string
	{
		$form = $this->current();
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
	public function renderFormEnd(bool $withTags = true): string
	{
		$form = $this->current();
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


	public function item($item): object
	{
		return is_object($item)
			? $item
			: $this->current()[$item];
	}


	public function begin(Container $form): void
	{
		$this->stack[] = $form;

		if ($form instanceof Form) {
			$form->fireRenderEvents();
			foreach ($form->getControls() as $control) {
				$control->setOption('rendered', false);
			}
		}
	}


	public function end(): void
	{
		array_pop($this->stack);
	}


	public function current(): Container
	{
		return end($this->stack) ?: throw new \LogicException('Form declaration is missing, did you use {form} or <form n:name> tag?');
	}
}
