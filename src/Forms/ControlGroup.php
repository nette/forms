<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;


/**
 * A user group of form controls.
 */
class ControlGroup
{
	use Nette\SmartObject;

	/** @var \SplObjectStorage */
	protected $controls;

	/** @var array user options */
	private $options = [];


	public function __construct()
	{
		$this->controls = new \SplObjectStorage;
	}


	/**
	 * @return static
	 */
	public function add(...$items)
	{
		foreach ($items as $item) {
			if ($item instanceof IControl) {
				$this->controls->attach($item);

			} elseif ($item instanceof Container) {
				foreach ($item->getComponents() as $component) {
					$this->add($component);
				}
			} elseif ($item instanceof \Traversable || is_array($item)) {
				$this->add(...$item);

			} else {
				$type = is_object($item) ? get_class($item) : gettype($item);
				throw new Nette\InvalidArgumentException("IControl or Container items expected, $type given.");
			}
		}
		return $this;
	}


	/**
	 * @return void
	 */
	public function remove(IControl $control)
	{
		$this->controls->detach($control);
	}


	/**
	 * @return void
	 */
	public function removeOrphans()
	{
		foreach ($this->controls as $control) {
			if (!$control->getForm(false)) {
				$this->controls->detach($control);
			}
		}
	}


	/**
	 * @return IControl[]
	 */
	public function getControls()
	{
		return iterator_to_array($this->controls);
	}


	/**
	 * Sets user-specific option.
	 * Options recognized by DefaultFormRenderer
	 * - 'label' - textual or IHtmlString object label
	 * - 'visual' - indicates visual group
	 * - 'container' - container as Html object
	 * - 'description' - textual or IHtmlString object description
	 * - 'embedNext' - describes how render next group
	 *
	 * @param  string
	 * @param  mixed
	 * @return static
	 */
	public function setOption($key, $value)
	{
		if ($value === null) {
			unset($this->options[$key]);

		} else {
			$this->options[$key] = $value;
		}
		return $this;
	}


	/**
	 * Returns user-specific option.
	 * @param  string
	 * @param  mixed
	 * @return mixed
	 */
	public function getOption($key, $default = null)
	{
		return isset($this->options[$key]) ? $this->options[$key] : $default;
	}


	/**
	 * Returns user-specific options.
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}
}
