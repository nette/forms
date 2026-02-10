<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte;

use Nette;
use Nette\Forms\Container;
use Nette\Forms\Control;
use Nette\Forms\Form;
use Nette\Utils\Html;
use function end, explode, is_object, parse_url, preg_replace, preg_split, urldecode;
use const PHP_URL_QUERY, PREG_SPLIT_NO_EMPTY;


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
	 * @param array<string, mixed>  $attrs
	 */
	public function renderFormBegin(array $attrs, bool $withTags = true): string
	{
		$form = $this->current();
		if (!$form instanceof Form) {
			throw new Nette\ShouldNotHappenException;
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
	public function renderFormEnd(bool $withTags = true): string
	{
		$form = $this->current();
		if (!$form instanceof Form) {
			throw new Nette\ShouldNotHappenException;
		}

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


	public function item(object|string|int $item, string $type = Control::class): Control|Container
	{
		$item = is_object($item) ? $item : $this->current()[$item];
		if (!$item instanceof $type) {
			throw new Nette\InvalidArgumentException("Expected instance of $type, " . get_debug_type($item) . ' given.');
		}
		return $item;
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
		return end($this->stack) ?: throw new Nette\InvalidStateException('Form declaration is missing, did you use {form} or <form n:name> tag?');
	}
}
