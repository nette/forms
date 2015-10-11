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
class ControlGroup extends Nette\Object
{
	/** @var \SplObjectStorage */
	protected $controls;

	/** @var array user options */
	private $options = array();


	public function __construct()
	{
		$this->controls = new \SplObjectStorage;
	}


	/**
	 * @return self
	 */
	public function add()
	{
		foreach (func_get_args() as $num => $item) {
			if ($item instanceof IControl) {
				$this->controls->attach($item);

			} elseif ($item instanceof \Traversable || is_array($item)) {
				call_user_func_array(array($this, 'add'), is_array($item) ? $item : iterator_to_array($item));

			} else {
				$type = is_object($item) ? get_class($item) : gettype($item);
				throw new Nette\InvalidArgumentException("IControl items expected, $type given.");
			}
		}
		return $this;
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
	 * - 'label' - textual or Html object label
	 * - 'visual' - indicates visual group
	 * - 'container' - container as Html object
	 * - 'description' - textual or Html object description
	 * - 'embedNext' - describes how render next group
	 *
	 * @param  string key
	 * @param  mixed  value
	 * @return self
	 */
	public function setOption($key, $value)
	{
		if ($value === NULL) {
			unset($this->options[$key]);

		} else {
			$this->options[$key] = $value;
		}
		return $this;
	}


	/**
	 * Returns user-specific option.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	public function getOption($key, $default = NULL)
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
