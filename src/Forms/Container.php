<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms;

use Nette;
use Nette\Utils\ArrayHash;
use Stringable;


/**
 * Container for form controls.
 *
 * @property   ArrayHash $values
 * @property-read \Iterator $controls
 * @property-read Form|null $form
 */
class Container extends Nette\ComponentModel\Container implements \ArrayAccess
{
	use Nette\ComponentModel\ArrayAccess;

	public const Array = 'array';

	/**
	 * Occurs when the form was validated
	 * @var array<callable(self, array|object): void|callable(array|object): void>
	 */
	public array $onValidate = [];
	protected ?ControlGroup $currentGroup = null;

	/** @var callable[]  extension methods */
	private static array $extMethods = [];
	private ?bool $validated = false;
	private ?string $mappedType = null;


	/********************* data exchange ****************d*g**/


	/**
	 * Fill-in with default values.
	 */
	public function setDefaults(array|object $data, bool $erase = false): static
	{
		$form = $this->getForm(false);
		if (!$form || !$form->isAnchored() || !$form->isSubmitted()) {
			$this->setValues($data, $erase);
		}

		return $this;
	}


	/**
	 * Fill-in with values.
	 * @internal
	 */
	public function setValues(array|object $data, bool $erase = false): static
	{
		if ($data instanceof \Traversable) {
			$values = iterator_to_array($data);

		} elseif (is_object($data) || is_array($data) || $data === null) {
			$values = (array) $data;
		}

		foreach ($this->getComponents() as $name => $control) {
			if ($control instanceof Control) {
				if (array_key_exists($name, $values)) {
					$control->setValue($values[$name]);

				} elseif ($erase) {
					$control->setValue(null);
				}
			} elseif ($control instanceof self) {
				if (isset($values[$name])) {
					$control->setValues($values[$name], $erase);

				} elseif ($erase) {
					$control->setValues([], $erase);
				}
			}
		}

		return $this;
	}


	/**
	 * Returns the values submitted by the form.
	 * @param  Control[]|null  $controls
	 */
	public function getValues(string|object|bool|null $returnType = null, ?array $controls = null): object|array
	{
		$form = $this->getForm(false);
		if ($form && ($submitter = $form->isSubmitted())) {
			if ($this->validated === null) {
				throw new Nette\InvalidStateException('You cannot call getValues() during the validation process. Use getUntrustedValues() instead.');

			} elseif (!$this->isValid()) {
				trigger_error(__METHOD__ . "() invoked but the form is not valid (form '{$this->getName()}').", E_USER_WARNING);
			}

			if ($controls === null && $submitter instanceof SubmitterControl) {
				$controls = $submitter->getValidationScope();
				if ($controls !== null && !in_array($this, $controls, true)) {
					$scope = $this;
					while (($scope = $scope->getParent()) instanceof self) {
						if (in_array($scope, $controls, true)) {
							$controls[] = $this;
							break;
						}
					}
				}
			}
		}

		if ($returnType === true) {
			trigger_error(static::class . '::' . __FUNCTION__ . "(true) is deprecated, use getValues('array').", E_USER_DEPRECATED);
			$returnType = self::Array;
		}

		return $this->getUntrustedValues($returnType, $controls);
	}


	/**
	 * Returns the potentially unvalidated values submitted by the form.
	 * @param  Control[]|null  $controls
	 */
	public function getUntrustedValues(string|object|null $returnType = null, ?array $controls = null): object|array
	{
		if (is_object($returnType)) {
			$obj = $returnType;
			$rc = new \ReflectionClass($obj);

		} else {
			$returnType = ($returnType ?? $this->mappedType ?? ArrayHash::class);
			$rc = new \ReflectionClass($returnType === self::Array ? \stdClass::class : $returnType);
			if ($rc->hasMethod('__construct') && $rc->getMethod('__construct')->getNumberOfRequiredParameters()) {
				$obj = new \stdClass;
				$useConstructor = true;
			} else {
				$obj = $rc->newInstance();
			}
		}

		foreach ($this->getComponents() as $name => $control) {
			$allowed = $controls === null || in_array($this, $controls, true) || in_array($control, $controls, true);
			$name = (string) $name;
			if (
				$control instanceof Control
				&& $allowed
				&& !$control->isOmitted()
			) {
				$obj->$name = $control->getValue();

			} elseif ($control instanceof self) {
				$type = $returnType === self::Array && !$control->mappedType
					? self::Array
					: ($rc->hasProperty($name) ? Helpers::getSingleType($rc->getProperty($name)) : null);
				$obj->$name = $control->getUntrustedValues($type, $allowed ? null : $controls);
			}
		}

		if (isset($useConstructor)) {
			return new $returnType(...(array) $obj);
		}

		return $returnType === self::Array
			? (array) $obj
			: $obj;
	}


	/** @deprecated use getUntrustedValues() */
	public function getUnsafeValues($returnType, ?array $controls = null)
	{
		return $this->getUntrustedValues($returnType, $controls);
	}


	/** @return static */
	public function setMappedType(string $type): static
	{
		$this->mappedType = $type;
		return $this;
	}


	/********************* validation ****************d*g**/


	/**
	 * Is form valid?
	 */
	public function isValid(): bool
	{
		if ($this->validated === null) {
			throw new Nette\InvalidStateException('You cannot call isValid() during the validation process.');

		} elseif (!$this->validated) {
			if ($this->getErrors()) {
				return false;
			}

			$this->validate();
		}

		return !$this->getErrors();
	}


	/**
	 * Performs the server side validation.
	 * @param  Control[]|null  $controls
	 */
	public function validate(?array $controls = null): void
	{
		$this->validated = null;
		foreach ($controls ?? $this->getComponents() as $control) {
			if ($control instanceof Control || $control instanceof self) {
				$control->validate();
			}
		}

		$this->validated = true;

		foreach ($this->onValidate as $handler) {
			$params = Nette\Utils\Callback::toReflection($handler)->getParameters();
			$types = array_map(Helpers::getSingleType(...), $params);
			$args = isset($types[0]) && !$this instanceof $types[0]
				? [$this->getUntrustedValues($types[0])]
				: [$this, isset($params[1]) ? $this->getUntrustedValues($types[1]) : null];
			$handler(...$args);
		}
	}


	/**
	 * Returns all validation errors.
	 */
	public function getErrors(): array
	{
		$errors = [];
		foreach ($this->getControls() as $control) {
			$errors = array_merge($errors, $control->getErrors());
		}

		return array_unique($errors);
	}


	/********************* form building ****************d*g**/


	public function setCurrentGroup(?ControlGroup $group = null): static
	{
		$this->currentGroup = $group;
		return $this;
	}


	/**
	 * Returns current group.
	 */
	public function getCurrentGroup(): ?ControlGroup
	{
		return $this->currentGroup;
	}


	/**
	 * Adds the specified component to the IContainer.
	 * @throws Nette\InvalidStateException
	 */
	public function addComponent(
		Nette\ComponentModel\IComponent $component,
		?string $name,
		?string $insertBefore = null,
	): static
	{
		parent::addComponent($component, $name, $insertBefore);
		if ($this->currentGroup !== null) {
			$this->currentGroup->add($component);
		}

		return $this;
	}


	/**
	 * Iterates over all form controls.
	 */
	public function getControls(): \Iterator
	{
		return $this->getComponents(true, Control::class);
	}


	/**
	 * Returns form.
	 * @return ($throw is true ? Form : ?Form)
	 */
	public function getForm(bool $throw = true): ?Form
	{
		return $this->lookup(Form::class, $throw);
	}


	/********************* control factories ****************d*g**/


	/**
	 * Adds single-line text input control to the form.
	 */
	public function addText(
		string $name,
		string|Stringable|null $label = null,
		?int $cols = null,
		?int $maxLength = null,
	): Controls\TextInput
	{
		return $this[$name] = (new Controls\TextInput($label, $maxLength))
			->setHtmlAttribute('size', $cols);
	}


	/**
	 * Adds single-line text input control used for sensitive input such as passwords.
	 */
	public function addPassword(
		string $name,
		string|Stringable|null $label = null,
		?int $cols = null,
		?int $maxLength = null,
	): Controls\TextInput
	{
		return $this[$name] = (new Controls\TextInput($label, $maxLength))
			->setHtmlAttribute('size', $cols)
			->setHtmlType('password');
	}


	/**
	 * Adds multi-line text input control to the form.
	 */
	public function addTextArea(
		string $name,
		string|Stringable|null $label = null,
		?int $cols = null,
		?int $rows = null,
	): Controls\TextArea
	{
		return $this[$name] = (new Controls\TextArea($label))
			->setHtmlAttribute('cols', $cols)->setHtmlAttribute('rows', $rows);
	}


	/**
	 * Adds input for email.
	 */
	public function addEmail(string $name, string|Stringable|null $label = null): Controls\TextInput
	{
		return $this[$name] = (new Controls\TextInput($label))
			->addRule(Form::Email);
	}


	/**
	 * Adds input for integer.
	 */
	public function addInteger(string $name, string|Stringable|null $label = null): Controls\TextInput
	{
		return $this[$name] = (new Controls\TextInput($label))
			->setNullable()
			->addRule(Form::Integer);
	}


	/**
	 * Adds input for float.
	 */
	public function addFloat(string $name, string|Stringable|null $label = null): Controls\TextInput
	{
		return $this[$name] = (new Controls\TextInput($label))
			->setNullable()
			->setHtmlType('number')
			->setHtmlAttribute('step', 'any')
			->addRule(Form::Float);
	}


	/**
	 * Adds input for date selection.
	 */
	public function addDate(string $name, string|object|null $label = null): Controls\DateTimeControl
	{
		return $this[$name] = new Controls\DateTimeControl($label, Controls\DateTimeControl::TypeDate);
	}


	/**
	 * Adds input for time selection.
	 */
	public function addTime(
		string $name,
		string|object|null $label = null,
		bool $withSeconds = false,
	): Controls\DateTimeControl
	{
		return $this[$name] = new Controls\DateTimeControl($label, Controls\DateTimeControl::TypeTime, $withSeconds);
	}


	/**
	 * Adds input for date and time selection.
	 */
	public function addDateTime(
		string $name,
		string|object|null $label = null,
		bool $withSeconds = false,
	): Controls\DateTimeControl
	{
		return $this[$name] = new Controls\DateTimeControl($label, Controls\DateTimeControl::TypeDateTime, $withSeconds);
	}


	/**
	 * Adds control that allows the user to upload files.
	 */
	public function addUpload(string $name, string|Stringable|null $label = null): Controls\UploadControl
	{
		return $this[$name] = new Controls\UploadControl($label, multiple: false);
	}


	/**
	 * Adds control that allows the user to upload multiple files.
	 */
	public function addMultiUpload(string $name, string|Stringable|null $label = null): Controls\UploadControl
	{
		return $this[$name] = new Controls\UploadControl($label, multiple: true);
	}


	/**
	 * Adds hidden form control used to store a non-displayed value.
	 */
	public function addHidden(string $name, $default = null): Controls\HiddenField
	{
		return $this[$name] = (new Controls\HiddenField)
			->setDefaultValue($default);
	}


	/**
	 * Adds check box control to the form.
	 */
	public function addCheckbox(string $name, string|Stringable|null $caption = null): Controls\Checkbox
	{
		return $this[$name] = new Controls\Checkbox($caption);
	}


	/**
	 * Adds set of radio button controls to the form.
	 */
	public function addRadioList(
		string $name,
		string|Stringable|null $label = null,
		?array $items = null,
	): Controls\RadioList
	{
		return $this[$name] = new Controls\RadioList($label, $items);
	}


	/**
	 * Adds set of checkbox controls to the form.
	 */
	public function addCheckboxList(
		string $name,
		string|Stringable|null $label = null,
		?array $items = null,
	): Controls\CheckboxList
	{
		return $this[$name] = new Controls\CheckboxList($label, $items);
	}


	/**
	 * Adds select box control that allows single item selection.
	 */
	public function addSelect(
		string $name,
		string|Stringable|null $label = null,
		?array $items = null,
		?int $size = null,
	): Controls\SelectBox
	{
		return $this[$name] = (new Controls\SelectBox($label, $items))
			->setHtmlAttribute('size', $size > 1 ? $size : null);
	}


	/**
	 * Adds select box control that allows multiple item selection.
	 */
	public function addMultiSelect(
		string $name,
		string|Stringable|null $label = null,
		?array $items = null,
		?int $size = null,
	): Controls\MultiSelectBox
	{
		return $this[$name] = (new Controls\MultiSelectBox($label, $items))
			->setHtmlAttribute('size', $size > 1 ? $size : null);
	}


	/**
	 * Adds color picker.
	 */
	public function addColor(string $name, string|Stringable|null $label = null): Controls\ColorPicker
	{
		return $this[$name] = new Controls\ColorPicker($label);
	}


	/**
	 * Adds button used to submit form.
	 */
	public function addSubmit(string $name, string|Stringable|null $caption = null): Controls\SubmitButton
	{
		return $this[$name] = new Controls\SubmitButton($caption);
	}


	/**
	 * Adds push buttons with no default behavior.
	 */
	public function addButton(string $name, string|Stringable|null $caption = null): Controls\Button
	{
		return $this[$name] = new Controls\Button($caption);
	}


	/**
	 * Adds graphical button used to submit form.
	 * @param  string|null  $src  URI of the image
	 * @param  string|null  $alt  alternate text for the image
	 */
	public function addImageButton(string $name, ?string $src = null, ?string $alt = null): Controls\ImageButton
	{
		return $this[$name] = new Controls\ImageButton($src, $alt);
	}


	/** @deprecated  use addImageButton() */
	public function addImage(): Controls\ImageButton
	{
		return $this->addImageButton(...func_get_args());
	}


	/**
	 * Adds naming container to the form.
	 */
	public function addContainer(string|int $name): self
	{
		$control = new self;
		$control->currentGroup = $this->currentGroup;
		if ($this->currentGroup !== null) {
			$this->currentGroup->add($control);
		}

		return $this[$name] = $control;
	}


	/********************* extension methods ****************d*g**/


	public function __call(string $name, array $args): mixed
	{
		if (isset(self::$extMethods[$name])) {
			return (self::$extMethods[$name])($this, ...$args);
		}

		return parent::__call($name, $args);
	}


	public static function extensionMethod(string $name, /*callable*/ $callback): void
	{
		if (str_contains($name, '::')) { // back compatibility
			[, $name] = explode('::', $name);
		}

		self::$extMethods[$name] = $callback;
	}


	/**
	 * Prevents cloning.
	 */
	public function __clone()
	{
		throw new Nette\NotImplementedException('Form cloning is not supported yet.');
	}
}
