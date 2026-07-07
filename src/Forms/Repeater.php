<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use Nette\Utils\Html;
use Stringable;
use function count;


/**
 * Container for a dynamic number of repeating form sections.
 *
 * Items of a submitted form are built when first needed: by validation, by reading or setting
 * values, by rendering or by accessing an item, so that bounds set after addRepeater() apply.
 */
class Repeater extends Container
{
	/** validation message keys */
	public const
		MinItems = 'forms.repeater.min',
		MaxItems = 'forms.repeater.max';

	/** how many items above the maximum are still built, so a genuine overshoot stays visible and removable */
	private const OverflowReserve = 8;

	/** maximum number of items of a repeater without an explicit maximum, a protection against forged requests */
	private const ItemsLimit = 1000;

	private int $minCount = 0;
	private ?int $maxCount = null;
	private int $defaultCount = 0;

	/** number of submitted items, null when the items were not loaded from a submission */
	private ?int $submittedCount = null;

	/** @var ?list<mixed> */
	private ?array $submittedData = null;

	/** @var list<string|Stringable> */
	private array $errors = [];

	/** item rendered in the <template> element, exists while the repeater is attached to a form */
	private ?Container $template = null;

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
		$this->monitor(
			Form::class,
			function (): void { $this->template = $this->createDetachedItem($this->getTemplateIndex()); },
			function (): void { $this->template = null; },
		);
	}


	/**
	 * Sets the minimum, maximum and default number of items. On a form that was not
	 * submitted, items are immediately created up to the default count.
	 */
	public function setBounds(int $min = 0, ?int $max = null, ?int $default = null): static
	{
		$default ??= $min;
		if ($min < 0) {
			throw new Nette\InvalidArgumentException("Minimum items count ($min) cannot be negative.");
		} elseif ($max !== null && $max < $min) {
			throw new Nette\InvalidArgumentException("Maximum items count ($max) cannot be less than minimum ($min).");
		} elseif ($default < $min || ($max !== null && $default > $max)) {
			throw new Nette\InvalidArgumentException("Default items count ($default) must be between minimum ($min) and maximum (" . ($max ?? 'unlimited') . ').');
		}

		$this->minCount = $min;
		$this->maxCount = $max;
		$this->defaultCount = $default;

		// submitted items are not padded with empty items (validate() reports the count violation instead)
		if ($this->submittedCount !== null) {
			$this->ensureMaxCount($this->getBuildLimit());
		} elseif (!$this->isLoadable()) {
			if ($max !== null) {
				$this->ensureMaxCount($max);
			}
			$this->ensureMinCount($default);
		}
		return $this;
	}


	/**
	 * Values are a list of items, their keys are ignored.
	 * @param  iterable<mixed>|\stdClass  $values
	 * @internal
	 */
	public function setValues(array|object $values, bool $erase = false, bool $onlyDisabled = false): static
	{
		$this->ensureLoaded();
		$values = array_values($values instanceof \Traversable ? iterator_to_array($values) : (array) $values);
		if (!$onlyDisabled) {
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
		$this->submittedCount = count($this->getSubmittedData());
		// a forged request must not invoke the (possibly expensive) factory for an unbounded
		// number of items; validate() reports the violation from the raw count anyway
		$build = min($this->submittedCount, $this->getBuildLimit());
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
	 * Discards the submitted items and creates the default ones, as on a form that was not submitted.
	 * @internal
	 */
	public function reset(): void
	{
		$this->submittedCount = $this->submittedData = null;
		$this->errors = [];
		$this->ensureMaxCount(0);
		$this->ensureMinCount($this->defaultCount);
	}


	/**
	 * Sets the class each item is mapped to. Unlike Container::setMappedType(), which maps the container
	 * itself, it makes getValues() return a list of these objects, also when 'array' is requested.
	 * Without it, the repeater returns an ArrayHash of ArrayHash items (or an array of arrays for 'array')
	 * indexed from 0.
	 * @param class-string  $type
	 */
	public function setMappedType(string $type): static
	{
		$this->itemMappedType = $type;
		return $this;
	}


	public function getUntrustedValues(string|object|null $returnType = null, ?array $controls = null): object|array
	{
		$this->ensureLoaded();
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
	 * Performs the server side validation including the number of submitted items,
	 * which is checked first, so that the onValidate handlers see its error.
	 * @param  (Control|Container)[]|null  $controls
	 */
	public function validate(?array $controls = null): void
	{
		$this->ensureLoaded();
		$this->errors = [];
		if ($this->submittedCount !== null) {
			$max = $this->maxCount ?? self::ItemsLimit;
			if ($this->submittedCount < $this->minCount) {
				$this->addCountError(new Validation\Message(self::MinItems, 'Please add at least %count items.', count: $this->minCount));
			} elseif ($this->submittedCount > $max) {
				$this->addCountError(new Validation\Message(self::MaxItems, 'Please add no more than %count items.', count: $max));
			}
		}

		parent::validate($controls);
	}


	private function addCountError(Validation\Message $message): void
	{
		$this->addError(MessageFormatter::formatStandalone($message, $this->getForm()->getTranslator()), translate: false);
	}


	/**
	 * Adds an error concerning the repeater as a whole, such as the number of items.
	 * Containers have no place for errors in the form, so it is added to the form errors as well.
	 */
	public function addError(string|Stringable $message, bool $translate = true): void
	{
		$form = $this->getForm();
		if ($translate && ($translator = $form->getTranslator())) {
			$message = $translator->translate($message);
		}

		$this->errors[] = $message;
		$form->addError($message, translate: false);
	}


	/**
	 * Returns errors of the repeater itself, excluding errors of the item controls.
	 * @return list<string|Stringable>
	 */
	public function getOwnErrors(): array
	{
		return array_values(array_unique($this->errors));
	}


	/**
	 * Returns errors of the repeater, of the nested repeaters and of the item controls.
	 * @return list<string|Stringable>
	 */
	public function getErrors(): array
	{
		$errors = $this->errors;
		foreach ($this->getComponentTree() as $component) {
			if ($component instanceof Control || $component instanceof self) {
				$errors = [...$errors, ...($component instanceof self ? $component->errors : $component->getErrors())];
			}
		}

		return array_values(array_unique($errors));
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


	protected function createComponent(string $name): ?Nette\ComponentModel\IComponent
	{
		$this->ensureLoaded();
		return parent::createComponent($name);
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


	/**
	 * Creates an item that is rendered in the context of the form but never becomes its part: the item
	 * knows its parent, the repeater does not list it, so it is not validated, has no values and keeps
	 * the default values of the factory even on a submitted form.
	 */
	private function createDetachedItem(string $name): Container
	{
		$item = new Container;
		($this->factory)($item);
		$defaults = [];
		foreach ($item->getComponentTree() as $component) {
			if ($component instanceof Control) {
				$defaults[] = [$component, $component->getValue()];
			}
		}

		$item->setParent($this, $name);
		foreach ($defaults as [$control, $value]) {
			$control->setValue($value); // discards submitted data loaded on attaching
		}

		return $item;
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


	private function ensureLoaded(): void
	{
		if ($this->submittedCount === null && $this->isLoadable()) {
			$this->loadHttpData();
		}
	}


	/**
	 * Tells whether the items are built from submitted data, i.e. the form is submitted
	 * and the repeater does not lie in a detached item.
	 */
	private function isLoadable(): bool
	{
		$form = $this->getForm(throw: false);
		return $form && !$this->isDetached() && $form->isAnchored() && $form->isSubmitted();
	}


	private function isDetached(): bool
	{
		for ($component = $this; $parent = $component->getParent(); $component = $parent) {
			if (($parent->getComponents()[(string) $component->getName()] ?? null) !== $component) {
				return true;
			}
		}

		return false;
	}


	private function getBuildLimit(): int
	{
		return ($this->maxCount ?? self::ItemsLimit) + self::OverflowReserve;
	}


	/********************* rendering ****************d*g**/


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
		return $this->renderEach(function (Container $item) use ($renderer): void {
			$el = (clone $this->itemPrototype)->data('repeater-index', $item->getName());
			echo $el->startTag();
			$renderer($item);
			echo $this->renderItemEnd($item), $el->endTag(), "\n";
		});
	}


	/**
	 * Renders the items, the empty items offered up to the minimum after an invalid submission
	 * and the <template> element. The renderer prints a whole item including its wrapper element,
	 * which carries the item name in the data-repeater-index attribute; in the template, the name is
	 * a placeholder that the client replaces with the index in all attributes of a new item.
	 * @param  \Closure(Container): void  $renderer
	 * @internal
	 */
	public function renderEach(\Closure $renderer): string
	{
		$this->ensureLoaded();
		$items = $this->getComponents();
		if ($this->submittedCount !== null) {
			for ($i = count($items); $i < $this->minCount; $i++) {
				$items[$i] = $this->createDetachedItem((string) $i);
			}
		}

		$template = $this->getTemplate();
		foreach ($template->getControls() as $control) {
			$control->setOption('rendered', false);
		}

		ob_start();
		foreach ($items as $item) {
			assert($item instanceof Container);
			$renderer($item);
		}

		echo "<template>\n";
		$renderer($template);
		echo "</template>\n";
		return ob_get_clean();
	}


	/**
	 * Renders the hidden fields of the item that the renderer did not print, so that they are removed
	 * together with the item, and the datalists of a detached item, which the form end does not know.
	 * @internal
	 */
	public function renderItemEnd(Container $item): string
	{
		$detached = !in_array($item, $this->getComponents(), true);
		$s = '';
		foreach ($item->getControls() as $control) {
			if ($control->getOption('type') === 'hidden' && !$control->getOption('rendered')) {
				$s .= $control->getControl();
			} elseif ($detached && $control instanceof Controls\TextInput && $control->getOption('rendered')) {
				$s .= $control->getDatalist();
			}
		}

		return $s;
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


	/**
	 * Returns the item rendered in the <template> element. It lives in the form like the other items
	 * (translator, HTML names and ids, monitors of controls), but is not one of them.
	 * @internal
	 */
	public function getTemplate(): Container
	{
		return $this->template ?? throw new Nette\InvalidStateException('Repeater is not attached to a form.');
	}


	/**
	 * Returns the name of the template item, unique among the nested repeaters.
	 */
	private function getTemplateIndex(): string
	{
		$depth = 0;
		for ($parent = $this->getParent(); $parent; $parent = $parent->getParent()) {
			$depth += $parent instanceof self ? 1 : 0;
		}

		return "__index{$depth}__";
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
