<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Rendering;

use Nette;
use Nette\HtmlStringable;
use Nette\Utils\Html;


/**
 * Converts a Form into the HTML output.
 */
class DefaultFormRenderer implements Nette\Forms\FormRenderer
{
	use Nette\SmartObject;

	/**
	 *  /--- form.container
	 *
	 *    /--- error.container
	 *      .... error.item [.class]
	 *    \---
	 *
	 *    /--- hidden.container
	 *      .... HIDDEN CONTROLS
	 *    \---
	 *
	 *    /--- group.container
	 *      .... group.label
	 *      .... group.description
	 *
	 *      /--- controls.container
	 *
	 *        /--- pair.container [.required .optional .odd]
	 *
	 *          /--- label.container
	 *            .... LABEL
	 *            .... label.suffix
	 *            .... label.requiredsuffix
	 *          \---
	 *
	 *          /--- control.container [.odd .multi]
	 *            .... CONTROL [.required .error .text .password .file .submit .button]
	 *            .... control.requiredsuffix
	 *            .... control.description
	 *            .... control.errorcontainer + control.erroritem
	 *          \---
	 *        \---
	 *      \---
	 *    \---
	 *  \--
	 * @var array of HTML tags */
	public $wrappers = [
		'form' => [
			'container' => null,
		],

		'error' => [
			'container' => 'ul class=error',
			'item' => 'li',
		],

		'group' => [
			'container' => 'fieldset',
			'label' => 'legend',
			'description' => 'p',
		],

		'controls' => [
			'container' => 'table',
		],

		'pair' => [
			'container' => 'tr',
			'.required' => 'required',
			'.optional' => null,
			'.odd' => null,
			'.error' => null,
		],

		'control' => [
			'container' => 'td',
			'.odd' => null,
			'.multi' => null,

			'description' => 'small',
			'requiredsuffix' => '',
			'errorcontainer' => 'span class=error',
			'erroritem' => '',

			'.required' => 'required',
			'.error' => null,
			'.text' => 'text',
			'.password' => 'text',
			'.file' => 'text',
			'.email' => 'text',
			'.number' => 'text',
			'.submit' => 'button',
			'.image' => 'imagebutton',
			'.button' => 'button',
		],

		'label' => [
			'container' => 'th',
			'suffix' => null,
			'requiredsuffix' => '',
		],

		'hidden' => [
			'container' => null,
		],
	];

	/** @var Nette\Forms\Form */
	protected $form;

	/** @var int */
	protected $counter;


	/**
	 * Provides complete form rendering.
	 * @param  string  $mode  'begin', 'errors', 'ownerrors', 'body', 'end' or empty to render all
	 */
	public function render(Nette\Forms\Form $form, string $mode = null): string
	{
		if ($this->form !== $form) {
			$this->form = $form;
		}

		$s = '';
		if (!$mode || $mode === 'begin') {
			$s .= $this->renderBegin();
		}
		if (!$mode || strtolower($mode) === 'ownerrors') {
			$s .= $this->renderErrors();

		} elseif ($mode === 'errors') {
			$s .= $this->renderErrors(null, false);
		}
		if (!$mode || $mode === 'body') {
			$s .= $this->renderBody();
		}
		if (!$mode || $mode === 'end') {
			$s .= $this->renderEnd();
		}
		return $s;
	}


	/**
	 * Renders form begin.
	 */
	public function renderBegin(): string
	{
		$this->counter = 0;

		foreach ($this->form->getControls() as $control) {
			$control->setOption('rendered', false);
		}

		if ($this->form->isMethod('get')) {
			$el = clone $this->form->getElementPrototype();
			$el->action = (string) $el->action;
			$query = parse_url($el->action, PHP_URL_QUERY) ?: '';
			$el->action = str_replace("?$query", '', $el->action);
			$s = '';
			foreach (preg_split('#[;&]#', $query, -1, PREG_SPLIT_NO_EMPTY) as $param) {
				$parts = explode('=', $param, 2);
				$name = urldecode($parts[0]);
				$prefix = explode('[', $name, 2)[0];
				if (!isset($this->form[$prefix])) {
					$s .= Html::el('input', ['type' => 'hidden', 'name' => $name, 'value' => urldecode($parts[1])]);
				}
			}
			return $el->startTag() . ($s ? "\n\t" . $this->getWrapper('hidden container')->setHtml($s) : '');

		} else {
			return $this->form->getElementPrototype()->startTag();
		}
	}


	/**
	 * Renders form end.
	 */
	public function renderEnd(): string
	{
		$s = '';
		foreach ($this->form->getControls() as $control) {
			if ($control->getOption('type') === 'hidden' && !$control->getOption('rendered')) {
				$s .= $control->getControl();
			}
		}
		if (iterator_count($this->form->getComponents(true, Nette\Forms\Controls\TextInput::class)) < 2) {
			$s .= '<!--[if IE]><input type=IEbug disabled style="display:none"><![endif]-->';
		}
		if ($s) {
			$s = $this->getWrapper('hidden container')->setHtml($s) . "\n";
		}

		return $s . $this->form->getElementPrototype()->endTag() . "\n";
	}


	/**
	 * Renders validation errors (per form or per control).
	 */
	public function renderErrors(Nette\Forms\Control $control = null, bool $own = true): string
	{
		$errors = $control
			? $control->getErrors()
			: ($own ? $this->form->getOwnErrors() : $this->form->getErrors());
		return $this->doRenderErrors($errors, (bool) $control);
	}


	private function doRenderErrors(array $errors, bool $control): string
	{
		if (!$errors) {
			return '';
		}
		$container = $this->getWrapper($control ? 'control errorcontainer' : 'error container');
		$item = $this->getWrapper($control ? 'control erroritem' : 'error item');

		foreach ($errors as $error) {
			$item = clone $item;
			if ($error instanceof HtmlStringable) {
				$item->addHtml($error);
			} else {
				$item->setText($error);
			}
			$container->addHtml($item);
		}

		return $control
			? "\n\t" . $container->render()
			: "\n" . $container->render(0);
	}


	/**
	 * Renders form body.
	 */
	public function renderBody(): string
	{
		$s = $remains = '';

		$defaultContainer = $this->getWrapper('group container');
		$translator = $this->form->getTranslator();

		foreach ($this->form->getGroups() as $group) {
			if (!$group->getControls() || !$group->getOption('visual')) {
				continue;
			}

			$container = $group->getOption('container', $defaultContainer);
			$container = $container instanceof Html
				? clone $container
				: Html::el($container);

			$id = $group->getOption('id');
			if ($id) {
				$container->id = $id;
			}

			$s .= "\n" . $container->startTag();

			$text = $group->getOption('label');
			if ($text instanceof HtmlStringable) {
				$s .= $this->getWrapper('group label')->addHtml($text);

			} elseif ($text != null) { // intentionally ==
				if ($translator !== null) {
					$text = $translator->translate($text);
				}
				$s .= "\n" . $this->getWrapper('group label')->setText($text) . "\n";
			}

			$text = $group->getOption('description');
			if ($text instanceof HtmlStringable) {
				$s .= $text;

			} elseif ($text != null) { // intentionally ==
				if ($translator !== null) {
					$text = $translator->translate($text);
				}
				$s .= $this->getWrapper('group description')->setText($text) . "\n";
			}

			$s .= $this->renderControls($group);

			$remains = $container->endTag() . "\n" . $remains;
			if (!$group->getOption('embedNext')) {
				$s .= $remains;
				$remains = '';
			}
		}

		$s .= $remains . $this->renderControls($this->form);

		$container = $this->getWrapper('form container');
		$container->setHtml($s);
		return $container->render(0);
	}


	/**
	 * Renders group of controls.
	 * @param  Nette\Forms\Container|Nette\Forms\ControlGroup  $parent
	 */
	public function renderControls($parent): string
	{
		if (!($parent instanceof Nette\Forms\Container || $parent instanceof Nette\Forms\ControlGroup)) {
			throw new Nette\InvalidArgumentException('Argument must be Nette\Forms\Container or Nette\Forms\ControlGroup instance.');
		}

		$container = $this->getWrapper('controls container');

		$buttons = null;
		foreach ($parent->getControls() as $control) {
			if (
				$control->getOption('rendered')
				|| $control->getOption('type') === 'hidden'
				|| $control->getForm(false) !== $this->form
			) {
				// skip

			} elseif ($control->getOption('type') === 'button') {
				$buttons[] = $control;

			} else {
				if ($buttons) {
					$container->addHtml($this->renderPairMulti($buttons));
					$buttons = null;
				}
				$container->addHtml($this->renderPair($control));
			}
		}

		if ($buttons) {
			$container->addHtml($this->renderPairMulti($buttons));
		}

		$s = '';
		if (count($container)) {
			$s .= "\n" . $container . "\n";
		}

		return $s;
	}


	/**
	 * Renders single visual row.
	 */
	public function renderPair(Nette\Forms\Control $control): string
	{
		$pair = $this->getWrapper('pair container');
		$pair->addHtml($this->renderLabel($control));
		$pair->addHtml($this->renderControl($control));
		$pair->class($this->getValue($control->isRequired() ? 'pair .required' : 'pair .optional'), true);
		$pair->class($control->hasErrors() ? $this->getValue('pair .error') : null, true);
		$pair->class($control->getOption('class'), true);
		if (++$this->counter % 2) {
			$pair->class($this->getValue('pair .odd'), true);
		}
		$pair->id = $control->getOption('id');
		return $pair->render(0);
	}


	/**
	 * Renders single visual row of multiple controls.
	 * @param  Nette\Forms\Control[]  $controls
	 */
	public function renderPairMulti(array $controls): string
	{
		$s = [];
		foreach ($controls as $control) {
			if (!$control instanceof Nette\Forms\Control) {
				throw new Nette\InvalidArgumentException('Argument must be array of Nette\Forms\IControl instances.');
			}
			$description = $control->getOption('description');
			if ($description instanceof HtmlStringable) {
				$description = ' ' . $description;

			} elseif ($description != null) { // intentionally ==
				if ($control instanceof Nette\Forms\Controls\BaseControl) {
					$description = $control->translate($description);
				}
				$description = ' ' . $this->getWrapper('control description')->setText($description);

			} else {
				$description = '';
			}

			$control->setOption('rendered', true);
			$el = $control->getControl();
			if ($el instanceof Html) {
				if ($el->getName() === 'input') {
					$el->class($this->getValue("control .$el->type"), true);
				}
				$el->class($this->getValue('control .error'), $control->hasErrors());
			}
			$s[] = $el . $description;
		}
		$pair = $this->getWrapper('pair container');
		$pair->addHtml($this->renderLabel($control));
		$pair->addHtml($this->getWrapper('control container')->setHtml(implode(' ', $s)));
		return $pair->render(0);
	}


	/**
	 * Renders 'label' part of visual row of controls.
	 */
	public function renderLabel(Nette\Forms\Control $control): Html
	{
		$suffix = $this->getValue('label suffix') . ($control->isRequired() ? $this->getValue('label requiredsuffix') : '');
		$label = $control->getLabel();
		if ($label instanceof Html) {
			$label->addHtml($suffix);
			if ($control->isRequired()) {
				$label->class($this->getValue('control .required'), true);
			}
		} elseif ($label != null) { // @intentionally ==
			$label .= $suffix;
		}
		return $this->getWrapper('label container')->setHtml((string) $label);
	}


	/**
	 * Renders 'control' part of visual row of controls.
	 */
	public function renderControl(Nette\Forms\Control $control): Html
	{
		$body = $this->getWrapper('control container');
		if ($this->counter % 2) {
			$body->class($this->getValue('control .odd'), true);
		}
		if (!$this->getWrapper('pair container')->getName()) {
			$body->class($control->getOption('class'), true);
			$body->id = $control->getOption('id');
		}

		$description = $control->getOption('description');
		if ($description instanceof HtmlStringable) {
			$description = ' ' . $description;

		} elseif ($description != null) { // intentionally ==
			if ($control instanceof Nette\Forms\Controls\BaseControl) {
				$description = $control->translate($description);
			}
			$description = ' ' . $this->getWrapper('control description')->setText($description);

		} else {
			$description = '';
		}

		if ($control->isRequired()) {
			$description = $this->getValue('control requiredsuffix') . $description;
		}

		$els = $errors = [];
		renderControl:
		$control->setOption('rendered', true);
		$el = $control->getControl();
		if ($el instanceof Html) {
			if ($el->getName() === 'input') {
				$el->class($this->getValue("control .$el->type"), true);
			}
			$el->class($this->getValue('control .error'), $control->hasErrors());
		}
		$els[] = $el;
		$errors = array_merge($errors, $control->getErrors());

		if ($nextTo = $control->getOption('nextTo')) {
			$control = $control->getForm()->getComponent($nextTo);
			$body->class($this->getValue('control .multi'), true);
			goto renderControl;
		}

		return $body->setHtml(implode('', $els) . $description . $this->doRenderErrors($errors, true));
	}


	public function getWrapper(string $name): Html
	{
		$data = $this->getValue($name);
		return $data instanceof Html ? clone $data : Html::el($data);
	}


	/** @return mixed */
	protected function getValue(string $name)
	{
		$name = explode(' ', $name);
		$data = &$this->wrappers[$name[0]][$name[1]];
		return $data;
	}
}
