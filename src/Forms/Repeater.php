<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use Nette\Utils\Html;
use function count;


/**
 * Container for a dynamic number of repeating form sections.
 */
class Repeater extends Container
{
	/** validation message keys */
	public const
		MinItems = ':repeaterMinItems',
		MaxItems = ':repeaterMaxItems';

	/** how many items above the maximum are still built, so a genuine overshoot stays visible and removable */
	private const OverflowReserve = 8;

	private int $minCount = 0;
	private ?int $maxCount = null;

	/** number of submitted items, null when the form was not submitted */
	private ?int $submittedCount = null;

	/** @var ?list<mixed> */
	private ?array $submittedData = null;

	/** @var ?class-string */
	private ?string $itemMappedType = null;
	private Html $containerPrototype;
	private Html $itemPrototype;
	private ?Html $addButton = null;
	private ?Html $removeButton = null;


	public function __construct(
		/** @var \Closure(Container): void */
		private \Closure $factory,
	) {
		$this->containerPrototype = Html::el('div');
		$this->itemPrototype = Html::el('div');
		$this->monitor(Form::class, function (Form $form): void {
			if ($form->isAnchored() && $form->isSubmitted()) {
				$this->loadHttpData();
			}
		});
	}


	/**
	 * Sets the minimum, maximum and default number of items. On a form that was not
	 * submitted, items are immediately created up to the default count.
	 */
	public function setBounds(int $min = 0, ?int $max = null, ?int $default = null): static
	{
		$default ??= $min;
		if ($max !== null && $default > $max) {
			throw new Nette\InvalidArgumentException("Default items count ($default) cannot be greater than maximum ($max).");
		}

		$this->minCount = $min;
		$this->maxCount = $max;

		// submitted items are not dropped below the cap of max + reserve and missing ones are not
		// padded with empty items (validate() reports the count violation instead)
		if ($this->submittedCount === null) {
			if ($max !== null) {
				$this->ensureMaxCount($max);
			}
			$this->ensureMinCount($default);
		} elseif ($max !== null) {
			// late bounds on an already submitted form: drop the containers built beyond the cap
			$this->ensureMaxCount($max + self::OverflowReserve);
		}
		return $this;
	}


	/**
	 * @param  iterable<mixed>|\stdClass  $values
	 * @internal
	 */
	public function setValues(array|object $values, bool $erase = false, bool $onlyDisabled = false): static
	{
		if (!$onlyDisabled) {
			$values = $values instanceof \Traversable ? iterator_to_array($values) : (array) $values;
			if ($erase) {
				$this->ensureMaxCount(count($values));
			}
			$this->ensureMinCount(count($values));
		}
		return parent::setValues($values, $erase, $onlyDisabled);
	}


	/**
	 * Creates child containers according to the number of submitted items.
	 */
	public function loadHttpData(): void
	{
		$data = $this->getSubmittedData();
		$this->submittedCount = count($data);
		// a forged request must not invoke the (possibly expensive) factory for an unbounded
		// number of items; validate() reports the violation from the raw count anyway
		$build = $this->maxCount === null
			? $this->submittedCount
			: min($this->submittedCount, $this->maxCount + self::OverflowReserve);
		$this->ensureMaxCount($build);
		$this->ensureMinCount($build);
	}


	/**
	 * Returns the raw submitted HTTP data with items renumbered to a continuous sequence from 0.
	 * Individual values are unsanitized; controls sanitize them when reading.
	 * @return list<mixed>
	 */
	public function getSubmittedData(): array
	{
		return $this->submittedData ??= array_values(parent::getSubmittedData());
	}


	/**
	 * Sets the class that individual items are mapped to when getValues() is called;
	 * the repeater itself then returns a list of these objects.
	 * @param class-string  $type
	 */
	public function setMappedType(string $type): static
	{
		$this->itemMappedType = $type;
		return $this;
	}


	public function getUntrustedValues(string|object|null $returnType = null, ?array $controls = null): object|array
	{
		if ($this->itemMappedType === null) {
			return parent::getUntrustedValues($returnType, $controls);
		}

		$items = [];
		foreach ($this->getComponents() as $container) {
			assert($container instanceof Container);
			$allowed = $controls === null
				|| in_array($this, $controls, true)
				|| in_array($container, $controls, true);
			$items[] = $container->getUntrustedValues($this->itemMappedType, $allowed ? null : $controls);
		}

		return $items;
	}


	/**
	 * Performs the server side validation including the number of submitted items.
	 * @param  (Control|Container)[]|null  $controls
	 */
	public function validate(?array $controls = null): void
	{
		parent::validate($controls);

		if ($this->submittedCount === null) {
			return;
		}

		if ($this->submittedCount < $this->minCount) {
			$this->addCountError(Validator::$messages[self::MinItems], $this->minCount);
		}
		if ($this->maxCount !== null && $this->submittedCount > $this->maxCount) {
			$this->addCountError(Validator::$messages[self::MaxItems], $this->maxCount);
		}
	}


	private function addCountError(string $message, int $count): void
	{
		$form = $this->getForm();
		if ($translator = $form->getTranslator()) {
			$message = (string) $translator->translate($message, $count);
		}
		$form->addError(sprintf($message, $count), translate: false);
	}


	/**
	 * Only item containers can be added; controls belong to the factory closure.
	 */
	public function addComponent(
		Nette\ComponentModel\IComponent $component,
		?string $name,
		?string $insertBefore = null,
	): static
	{
		if (!$component instanceof Container || $component instanceof Form) {
			throw new Nette\InvalidStateException(
				'Repeater accepts only item containers, add controls in the factory passed to addRepeater().',
			);
		}

		return parent::addComponent($component, $name, $insertBefore);
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


	private function ensureMinCount(int $count): void
	{
		for ($i = count($this->getComponents()); $i < $count; $i++) {
			$this->createItem($i);
		}
	}


	private function ensureMaxCount(int $count): void
	{
		foreach (array_slice($this->getComponents(), $count) as $component) {
			$this->removeComponent($component);
		}
	}


	/**
	 * Renders all items and the template for new ones using the given renderer, which prints the item content.
	 * @param  \Closure(Container): void  $renderer
	 */
	public function render(\Closure $renderer): string
	{
		$container = (clone $this->containerPrototype)->addAttributes($this->getContainerAttributes());
		return $container->startTag() . "\n"
			. $this->renderItems($renderer)
			. $container->endTag() . "\n";
	}


	/**
	 * Renders the items wrapped in the item prototype and the template for new ones.
	 * @param  \Closure(Container): void  $renderer
	 */
	public function renderItems(\Closure $renderer): string
	{
		$this->prepareForRendering();

		ob_start();
		foreach ($this->getComponents() as $index => $component) {
			assert($component instanceof Container);
			$item = (clone $this->itemPrototype)->data('repeater-index', (string) $index);
			echo $item->startTag();
			$renderer($component);
			echo $item->endTag(), "\n";
		}

		// the item wrapper is part of the template, so JS clones it including tag and attributes
		$item = (clone $this->itemPrototype)->data('repeater-index', '');
		echo "<template>\n", $item->startTag(), "\n";
		$renderer($this->createTemplate());
		echo $item->endTag(), "\n</template>\n";

		return ob_get_clean();
	}


	/**
	 * Returns the data attributes identifying the repeater for the client side.
	 * @return array<string, mixed>
	 */
	public function getContainerAttributes(): array
	{
		return [
			'data-nette-repeater' => $this->getHtmlPath(),
			'data-nette-repeater-min' => $this->minCount,
			'data-nette-repeater-max' => $this->maxCount,
		];
	}


	/** @internal */
	public function prepareForRendering(): void
	{
		if ($this->submittedCount !== null) {
			// re-rendering of an invalid submission shows the rows missing below the minimum
			$this->ensureMinCount($this->minCount);
		}
	}


	/**
	 * Creates a detached item container whose controls render with relative names, used for the <template> element.
	 * @internal
	 */
	public function createTemplate(): Container
	{
		$container = new Container;
		($this->factory)($container);
		$container->setParent((new Form)->setSubmissionSource(new Sources\NullSource), ''); // never submitted, controls never load HTTP data
		return $container;
	}


	public function getContainerPrototype(): Html
	{
		return $this->containerPrototype;
	}


	/**
	 * Returns the prototype of the element wrapping each item, e.g. set it to 'tr' for table layouts.
	 */
	public function getItemPrototype(): Html
	{
		return $this->itemPrototype;
	}


	/**
	 * Returns the prototype of the client-side button that adds a new item.
	 */
	public function getAddButtonPrototype(): Html
	{
		$this->addButton ??= Html::el('button', ['type' => 'button'])->setText('Add');
		if ($this->getParent()) {
			$this->addButton->data('nette-repeater-add', $this->getHtmlPath());
		}
		return $this->addButton;
	}


	/**
	 * Returns the prototype of the client-side button that removes an item.
	 */
	public function getRemoveButtonPrototype(): Html
	{
		return $this->removeButton ??= Html::el('button', ['type' => 'button'])
			->setText('Remove')
			->data('nette-repeater-remove', true);
	}


	/**
	 * Returns the path identifying this repeater in data-nette-repeater* attributes.
	 */
	private function getHtmlPath(): string
	{
		return ltrim($this->lookupPath(Form::class), self::NameSeparator);
	}
}
