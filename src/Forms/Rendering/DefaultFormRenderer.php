<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Rendering;

use Nette;
use Nette\Utils\Html;
use Nette\Utils\IHtmlString;


/**
 * Converts a Form into the HTML output.
 */
class DefaultFormRenderer implements Nette\Forms\IFormRenderer
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
	 *          /--- control.container [.odd]
	 *            .... CONTROL [.required .text .password .file .submit .button]
	 *            .... control.requiredsuffix
	 *            .... control.description
	 *            .... control.errorcontainer + control.erroritem
	 *          \---
	 *        \---
	 *      \---
	 *    \---
	 *  \--
	 *
	 * @var array of HTML tags */
	public $wrappers = [
		'form' => [
			'container' => NULL,
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
			'.optional' => NULL,
			'.odd' => NULL,
			'.error' => NULL,
		],

		'control' => [
			'container' => 'td',
			'.odd' => NULL,

			'description' => 'small',
			'requiredsuffix' => '',
			'errorcontainer' => 'span class=error',
			'erroritem' => '',

			'.required' => 'required',
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
			'suffix' => NULL,
			'requiredsuffix' => '',
		],

		'hidden' => [
			'container' => NULL,
		],
	];

	/** @var Nette\Forms\Form */
	protected $form;

	/** @var int */
	protected $counter;


	/**
	 * Provides complete form rendering.
	 * @param  Nette\Forms\Form
	 * @param  string 'begin', 'errors', 'ownerrors', 'body', 'end' or empty to render all
	 * @return string
	 */
	public function render(Nette\Forms\Form $form, $mode = NULL)
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
			$s .= $this->renderErrors(NULL, FALSE);
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
	 * @return string
	 */
	public function renderBegin()
	{
		$this->counter = 0;

		foreach ($this->form->getControls() as $control) {
			$control->setOption('rendered', FALSE);
		}

		if ($this->form->isMethod('get')) {
			$el = clone $this->form->getElementPrototype();
			$query = parse_url($el->action, PHP_URL_QUERY);
			$el->action = str_replace("?$query", '', $el->action);
			$s = '';
			foreach (preg_split('#[;&]#', $query, -1, PREG_SPLIT_NO_EMPTY) as $param) {
				$parts = explode('=', $param, 2);
				$name = urldecode($parts[0]);
				if (!isset($this->form[$name])) {
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
	 * @return string
	 */
	public function renderEnd()
	{
		$s = '';
		foreach ($this->form->getControls() as $control) {
			if ($control->getOption('type') === 'hidden' && !$control->getOption('rendered')) {
				$s .= $control->getControl();
			}
		}
		if (iterator_count($this->form->getComponents(TRUE, Nette\Forms\Controls\TextInput::class)) < 2) {
			$s .= '<!--[if IE]><input type=IEbug disabled style="display:none"><![endif]-->';
		}
		if ($s) {
			$s = $this->getWrapper('hidden container')->setHtml($s) . "\n";
		}

		return $s . $this->form->getElementPrototype()->endTag() . "\n";
	}


	/**
	 * Renders validation errors (per form or per control).
	 * @return string
	 */
	public function renderErrors(Nette\Forms\IControl $control = NULL, $own = TRUE)
	{
		$translator = $this->form->getTranslator();

		$errors = $control
			? $control->getErrors()
			: ($own ? $this->form->getOwnErrors() : $this->form->getErrors());
		if (!$errors) {
			return '';
		}
		$container = $this->getWrapper($control ? 'control errorcontainer' : 'error container');
		$item = $this->getWrapper($control ? 'control erroritem' : 'error item');

		foreach ($errors as $error) {
			$item = clone $item;
			if ($error instanceof IHtmlString) {
				$item->addHtml($error);
			} else {
				$item->setText($translator ? $translator->translate($error) : $error);
			}
			$container->addHtml($item);
		}
		return "\n" . $container->render($control ? 1 : 0);
	}


	/**
	 * Renders form body.
	 * @return string
	 */
	public function renderBody()
	{
		$s = $remains = '';

		$defaultContainer = $this->getWrapper('group container');
		$translator = $this->form->getTranslator();

		foreach ($this->form->getGroups() as $group) {
			if (!$group->getControls() || !$group->getOption('visual')) {
				continue;
			}

			$container = $group->getOption('container', $defaultContainer);
			$container = $container instanceof Html ? clone $container : Html::el($container);

			$id = $group->getOption('id');
			if ($id) {
				$container->id = $id;
			}

			$s .= "\n" . $container->startTag();

			$text = $group->getOption('label');
			if ($text instanceof IHtmlString) {
				$s .= $this->getWrapper('group label')->addHtml($text);

			} elseif (is_string($text)) {
				if ($translator !== NULL) {
					$text = $translator->translate($text);
				}
				$s .= "\n" . $this->getWrapper('group label')->setText($text) . "\n";
			}

			$text = $group->getOption('description');
			if ($text instanceof IHtmlString) {
				$s .= $text;

			} elseif (is_string($text)) {
				if ($translator !== NULL) {
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
	 * @param  Nette\Forms\Container|Nette\Forms\ControlGroup
	 * @return string
	 */
	public function renderControls($parent)
	{
		if (!($parent instanceof Nette\Forms\Container || $parent instanceof Nette\Forms\ControlGroup)) {
			throw new Nette\InvalidArgumentException('Argument must be Nette\Forms\Container or Nette\Forms\ControlGroup instance.');
		}

		$container = $this->getWrapper('controls container');

		$buttons = NULL;
		foreach ($parent->getControls() as $control) {
			if ($control->getOption('rendered') || $control->getOption('type') === 'hidden' || $control->getForm(FALSE) !== $this->form) {
				// skip

			} elseif ($control->getOption('type') === 'button') {
				$buttons[] = $control;

			} else {
				if ($buttons) {
					$container->addHtml($this->renderPairMulti($buttons));
					$buttons = NULL;
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
	 * @return string
	 */
	public function renderPair(Nette\Forms\IControl $control)
	{
		$pair = $this->getWrapper('pair container');
		$pair->addHtml($this->renderLabel($control));
		$pair->addHtml($this->renderControl($control));
		$pair->class($this->getValue($control->isRequired() ? 'pair .required' : 'pair .optional'), TRUE);
		$pair->class($control->hasErrors() ? $this->getValue('pair .error') : NULL, TRUE);
		$pair->class($control->getOption('class'), TRUE);
		if (++$this->counter % 2) {
			$pair->class($this->getValue('pair .odd'), TRUE);
		}
		$pair->id = $control->getOption('id');
		return $pair->render(0);
	}


	/**
	 * Renders single visual row of multiple controls.
	 * @param  Nette\Forms\IControl[]
	 * @return string
	 */
	public function renderPairMulti(array $controls)
	{
		$s = [];
		foreach ($controls as $control) {
			if (!$control instanceof Nette\Forms\IControl) {
				throw new Nette\InvalidArgumentException('Argument must be array of Nette\Forms\IControl instances.');
			}
			$description = $control->getOption('description');
			if ($description instanceof IHtmlString) {
				$description = ' ' . $description;

			} elseif (is_string($description)) {
				if ($control instanceof Nette\Forms\Controls\BaseControl) {
					$description = $control->translate($description);
				}
				$description = ' ' . $this->getWrapper('control description')->setText($description);

			} else {
				$description = '';
			}

			$control->setOption('rendered', TRUE);
			$el = $control->getControl();
			if ($el instanceof Html && $el->getName() === 'input') {
				$el->class($this->getValue("control .$el->type"), TRUE);
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
	 * @return Html
	 */
	public function renderLabel(Nette\Forms\IControl $control)
	{
		$suffix = $this->getValue('label suffix') . ($control->isRequired() ? $this->getValue('label requiredsuffix') : '');
		$label = $control->getLabel();
		if ($label instanceof Html) {
			$label->addHtml($suffix);
			if ($control->isRequired()) {
				$label->class($this->getValue('control .required'), TRUE);
			}
		} elseif ($label != NULL) { // @intentionally ==
			$label .= $suffix;
		}
		return $this->getWrapper('label container')->setHtml($label);
	}


	/**
	 * Renders 'control' part of visual row of controls.
	 * @return Html
	 */
	public function renderControl(Nette\Forms\IControl $control)
	{
		$body = $this->getWrapper('control container');
		if ($this->counter % 2) {
			$body->class($this->getValue('control .odd'), TRUE);
		}

		$description = $control->getOption('description');
		if ($description instanceof IHtmlString) {
			$description = ' ' . $description;

		} elseif (is_string($description)) {
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

		$control->setOption('rendered', TRUE);
		$el = $control->getControl();
		if ($el instanceof Html && $el->getName() === 'input') {
			$el->class($this->getValue("control .$el->type"), TRUE);
		}
		return $body->setHtml($el . $description . $this->renderErrors($control));
	}


	/**
	 * @param  string
	 * @return Html
	 */
	protected function getWrapper($name)
	{
		$data = $this->getValue($name);
		return $data instanceof Html ? clone $data : Html::el($data);
	}


	/**
	 * @param  string
	 * @return mixed
	 */
	protected function getValue($name)
	{
		$name = explode(' ', $name);
		$data = &$this->wrappers[$name[0]][$name[1]];
		return $data;
	}

}
