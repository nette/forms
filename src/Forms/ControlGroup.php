<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use function func_num_args;


/**
 * Named group of form controls, typically rendered as a fieldset.
 */
class ControlGroup
{
	/** @var \WeakMap<Control, null> */
	protected \WeakMap $controls;

	/** @var array<string, mixed> */
	private array $options = [];


	public function __construct()
	{
		$this->controls = new \WeakMap;
	}


	/** @param Control|Container|iterable<Control|Container> ...$items */
	public function add(Control|Container|iterable ...$items): static
	{
		foreach ($items as $item) {
			if ($item instanceof Control) {
				$this->controls[$item] = null;

			} elseif ($item instanceof Container) {
				foreach ($item->getComponents() as $component) {
					if ($component instanceof Control || $component instanceof Container) {
						$this->add($component);
					}
				}
			} else {
				$this->add(...$item);
			}
		}

		return $this;
	}


	public function remove(Control $control): void
	{
		unset($this->controls[$control]);
	}


	/**
	 * Removes controls that are no longer attached to a form.
	 */
	public function removeOrphans(): void
	{
		foreach ($this->controls as $control => $foo) {
			if (!$control->getForm(false)) {
				unset($this->controls[$control]);
			}
		}
	}


	/**
	 * Returns all controls in this group.
	 * @return list<Control>
	 */
	public function getControls(): array
	{
		$res = [];
		foreach ($this->controls as $control => $foo) {
			$res[] = $control;
		}
		return $res;
	}


	/**
	 * Sets a rendering option. Options recognized by DefaultFormRenderer:
	 * - 'label' - group label (string or HtmlStringable)
	 * - 'visual' - whether the group is rendered as a visual fieldset
	 * - 'container' - custom container Html element
	 * - 'description' - group description (string or HtmlStringable)
	 * - 'embedNext' - whether to embed the next group inside this group's container
	 */
	public function setOption(string $key, mixed $value): static
	{
		if ($value === null) {
			unset($this->options[$key]);

		} else {
			$this->options[$key] = $value;
		}

		return $this;
	}


	/**
	 * Returns a rendering option value.
	 */
	public function getOption(string $key): mixed
	{
		if (func_num_args() > 1) {
			trigger_error(__METHOD__ . '() parameter $default is deprecated, use operator ??', E_USER_DEPRECATED);
			$default = func_get_arg(1);
		}
		return $this->options[$key] ?? $default ?? null;
	}


	/**
	 * Returns all rendering options.
	 * @return array<string, mixed>
	 */
	public function getOptions(): array
	{
		return $this->options;
	}
}
