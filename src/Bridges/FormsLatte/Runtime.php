<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\FormsLatte;

use Nette;
use Nette\Forms\{Container, Control, Form};
use Nette\Utils\Html;
use function count, is_object;


/**
 * Runtime rendering helpers used by Latte.
 * @internal
 */
class Runtime
{
	/** @var Container[] */
	private array $stack = [];

	/** @var list<?string>  parallel to $stack; non-null element = id of a detached form */
	private array $detachedIds = [];

	/** @var list<bool>  parallel to $stack; true = scope the template's $form variable was bound to */
	private array $formBindings = [];


	/**
	 * Renders form begin.
	 * @param array<string, mixed>  $attrs
	 */
	public function renderFormBegin(array $attrs, bool $withTags = true): string
	{
		$form = $this->getScope();
		if (!$form instanceof Form) {
			throw new Nette\ShouldNotHappenException;
		}

		$el = $form->getElementPrototype();
		$el->action = (string) $el->action;
		$el = clone $el;
		if ($form->isMethod('get')) {
			$el->action = preg_replace('~\?[^#]*~', '', (string) $el->action, 1);
		}

		$el->addAttributes($attrs);
		return $withTags ? $el->startTag() : $el->attributes();
	}


	/**
	 * Renders form end.
	 */
	public function renderFormEnd(bool $withTags = true): string
	{
		$form = $this->getScope();
		if (!$form instanceof Form) {
			throw new Nette\ShouldNotHappenException;
		}

		$s = '';
		if ($form->isMethod('get')) {
			foreach (preg_split(
				'#[;&]#',
				(string) parse_url((string) $form->getElementPrototype()->action, PHP_URL_QUERY),
				-1,
				PREG_SPLIT_NO_EMPTY,
			) as $param) {
				$parts = explode('=', $param, 2);
				$name = urldecode($parts[0]);
				$prefix = explode('[', $name, 2)[0];
				if (!isset($form[$prefix])) {
					$s .= Html::el('input', ['type' => 'hidden', 'name' => $name, 'value' => urldecode($parts[1] ?? '')]);
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


	/**
	 * Resolves a control or container from the current form on the stack.
	 * @template T of Control|Container
	 * @param  class-string<T>  $type
	 * @return T
	 */
	public function get(object|string|int $item, string $type = Control::class): Control|Container
	{
		$item = is_object($item) ? $item : $this->getScope()[$item];
		if (!$item instanceof $type) {
			throw new Nette\InvalidArgumentException("Expected instance of $type, " . get_debug_type($item) . ' given.');
		}

		$detachedId = end($this->detachedIds);
		if ($detachedId !== null && $item instanceof Nette\Forms\Controls\BaseControl) {
			$item->setHtmlAttribute('form', $detachedId);
		}
		return $item;
	}


	public function begin(Container $form, bool $detached = false, ?\stdClass $global = null, bool $bindsForm = true): void
	{
		if ($global !== null) {
			$global->formsStack = &$this->stack; // BC alias for the removed formsStack provider
		}

		$this->stack[] = $form;
		$this->formBindings[] = $bindsForm;

		if ($form instanceof Form) {
			$form->fireRenderEvents();
			foreach ($form->getControls() as $control) {
				$control->setOption('rendered', false);
			}
		}

		// sub-containers inherit the parent's detached id; a nested Form starts fresh
		$detachedId = $form instanceof Form ? null : (end($this->detachedIds) ?: null);
		if ($detached) {
			if (!$form instanceof Form) {
				throw new Nette\InvalidStateException('Detached mode requires a Form instance.');
			}
			$detachedId = (string) $form->getElementPrototype()->id;
			if ($detachedId === '') {
				throw new Nette\InvalidStateException('Detached form must have an id; pass a name to the Form constructor or set it via getElementPrototype()->id.');
			}
		}
		$this->detachedIds[] = $detachedId;
	}


	/** Closes the current scope and returns the nearest enclosing scope bound to $form, if any. */
	public function end(): ?Container
	{
		array_pop($this->stack);
		array_pop($this->detachedIds);
		array_pop($this->formBindings);
		for ($i = count($this->stack) - 1; $i >= 0; $i--) {
			if ($this->formBindings[$i]) {
				return $this->stack[$i];
			}
		}

		return null;
	}


	public function getScope(): Container
	{
		return end($this->stack)
			?: throw new Nette\InvalidStateException('Form declaration is missing, did you use {form} or <form n:name> tag?');
	}


	/** Are we nested inside an already open form? */
	public function isNested(): bool
	{
		return (bool) $this->stack;
	}
}
