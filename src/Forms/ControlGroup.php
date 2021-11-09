<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms;

use Nette;


/**
 * A user group of form controls.
 */
final class ControlGroup
{
	protected \SplObjectStorage $controls;
	private array $options = [];


	public function __construct()
	{
		$this->controls = new \SplObjectStorage;
	}


	public function add(...$items): static
	{
		foreach ($items as $item) {
			if ($item instanceof Control) {
				$this->controls->attach($item);

			} elseif ($item instanceof Container) {
				foreach ($item->getComponents() as $component) {
					$this->add($component);
				}
			} elseif (is_iterable($item)) {
				$this->add(...$item);

			} else {
				$type = get_debug_type($item);
				throw new Nette\InvalidArgumentException("Control or Container items expected, $type given.");
			}
		}

		return $this;
	}


	public function remove(Control $control): void
	{
		$this->controls->detach($control);
	}


	public function removeOrphans(): void
	{
		foreach ($this->controls as $control) {
			if (!$control->getForm(false)) {
				$this->controls->detach($control);
			}
		}
	}


	/** @return Control[] */
	public function getControls(): array
	{
		return iterator_to_array($this->controls);
	}


	/**
	 * Sets user-specific option.
	 * Options recognized by DefaultFormRenderer
	 * - 'label' - textual or Nette\HtmlStringable object label
	 * - 'visual' - indicates visual group
	 * - 'container' - container as Html object
	 * - 'description' - textual or Nette\HtmlStringable object description
	 * - 'embedNext' - describes how render next group
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
	 * Returns user-specific option.
	 */
	public function getOption(string $key): mixed
	{
		if (func_num_args() > 1) {
			$default = func_get_arg(1);
		}
		return $this->options[$key] ?? $default ?? null;
	}


	/**
	 * Returns user-specific options.
	 */
	public function getOptions(): array
	{
		return $this->options;
	}
}
