<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms;

use Nette;
use Nette\Utils\Html;
use Stringable;


/**
 * Container for repeating form sections.
 */
class Repeater extends Container
{
	private int $minCount = 0;
	private ?int $maxCount = null;
	private int $defaultCount = 0;
	private Html $containerPrototype;

	/** @var array<string, Html>  */
	private array $buttons = [];


	/**
	 * @param  \Closure(Container): void  $factory
	 */
	public function __construct(
		private \Closure $factory,
	) {
		$this->containerPrototype = Html::el('div');
		$this->monitor(Form::class, function (Form $form): void {
			if ($form->isAnchored() && $form->isSubmitted()) {
				$this->loadHttpData();
			}
		});
	}


	/**
	 * Sets the minimum, maximum and default number of items.
	 */
	public function setBounds(int $min = 0, ?int $max = null, ?int $default = null): static
	{
		$default ??= $min;
		if ($max !== null) {
			if ($default > $max) {
				throw new Nette\InvalidArgumentException("Default items count ($default) cannot be greater than maximum ($max).");
			}
			$this->ensureMaxCount($max);
		}

		$this->minCount = $min;
		$this->maxCount = $max;
		$this->defaultCount = $default;
		$this->ensureMinCount($min);
		return $this;
	}


	/**
	 * Creates a single repeater item container.
	 */
	private function createItem(int $index): Container
	{
		$container = new Container;
		($this->factory)($container);
		$this->addComponent($container, (string) $index);
		return $container;
	}


	private function createTemplate(): Container
	{
		$container = new Container;
		($this->factory)($container);
		$container->setParent(new class extends Form {
			public function receiveHttpData(): array
			{
				return [];
			}
		}, '');
		return $container;
	}


	/**
	 * @internal
	 */
	public function setValues(array|object $values, bool $erase = false, bool $onlyDisabled = false): static
	{
		if (!$onlyDisabled) {
			$this->ensureMinCount(iterator_count($values));
		}
		return parent::setValues($values, $erase, $onlyDisabled);
	}


	public function loadHttpData(): void
	{
		$data = $this->getSubmittedData();
		$this->ensureMaxCount(count($data));
		$this->ensureMinCount(count($data));
	}


	public function getSubmittedData(): array
	{
		return array_values(parent::getSubmittedData());
	}


	private function ensureMinCount(int $count): void
	{
		$count = min($count, $this->maxCount ?? PHP_INT_MAX);
		for ($i = count($this->getComponents()); $i < $count; $i++) {
			$this->createItem($i);
		}
	}


	private function ensureMaxCount(int $count): void
	{
		$components = $this->getComponents();
		while (count($components) > $count) {
			$this->removeComponent(array_pop($components));
		}
	}


	public function render(\Closure $renderer): void
	{
		$container = (clone $this->containerPrototype)->addAttributes([
			'data-nette-repeater' => ltrim($this->lookupPath(Form::class), self::NameSeparator),
			'data-nette-repeater-min' => $this->minCount,
			'data-nette-repeater-max' => $this->maxCount,
		]);
		echo $container->startTag(), "\n";

		$this->ensureMinCount($this->defaultCount);
		foreach ($this->getComponents() as $index => $component) {
			echo '<div data-repeater-index="', htmlspecialchars((string) $index), '">';
			$renderer($component);
			echo "</div>\n";
		}

		echo "<template>\n";
		$renderer($this->createTemplate());
		echo "</template>\n";

		echo $container->endTag(), "\n";
	}


	public function getContainerPrototype(): Html
	{
		return $this->containerPrototype;
	}


	public function defineButton(string $name, string|Stringable $caption): Html
	{
		return $this->buttons[$name] = Html::el('button')->setText($caption);
	}


	/**
	 * Returns virtual control part for buttons.
	 */
	public function getButtonControl(string $name): Html
	{
		return match ($name) {
			'add' => ($this->buttons[$name] ?? $this->defineButton($name, 'Add'))
				->type('button')
				->data('nette-repeater-add', $this->getName()),
			'remove' => ($this->buttons[$name] ?? $this->defineButton($name, 'Remove'))
				->type('button')
				->data('nette-repeater-remove', true),
		};
	}
}
