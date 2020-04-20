<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Forms\Rules;
use Nette\Utils\Html;


/**
 * Base class that implements the basic functionality common to form controls.
 *
 * @property-read Form $form
 * @property-read string $htmlName
 * @property   mixed $htmlId
 * @property   mixed $value
 * @property   string|object $caption
 * @property   bool $disabled
 * @property   bool $omitted
 * @property-read Html $control
 * @property-read Html $label
 * @property-read Html $controlPrototype
 * @property-read Html $labelPrototype
 * @property   bool $required
 * @property-read bool $filled
 * @property-read array $errors
 * @property-read array $options
 * @property-read string $error
 */
abstract class BaseControl extends Nette\ComponentModel\Component implements IControl
{
	/** @var string */
	public static $idMask = 'frm-%s';

	/** @var mixed current control value */
	protected $value;

	/** @var Html  control element template */
	protected $control;

	/** @var Html  label element template */
	protected $label;

	/** @var bool */
	protected $disabled = false;

	/** @var callable[][]  extension methods */
	private static $extMethods = [];

	/** @var string|object textual caption or label */
	private $caption;

	/** @var array */
	private $errors = [];

	/** @var bool|null */
	private $omitted;

	/** @var Rules */
	private $rules;

	/** @var Nette\Localization\ITranslator */
	private $translator = true; // means autodetect

	/** @var array user options */
	private $options = [];


	/**
	 * @param  string|object  $caption
	 */
	public function __construct($caption = null)
	{
		$this->control = Html::el('input', ['type' => null, 'name' => null]);
		$this->label = Html::el('label');
		$this->caption = $caption;
		$this->rules = new Rules($this);
		$this->setValue(null);
		$this->monitor(Form::class, function (Form $form): void {
			if (!$this->isDisabled() && $form->isAnchored() && $form->isSubmitted()) {
				$this->loadHttpData();
			}
		});
	}


	/**
	 * Sets textual caption or label.
	 * @param object|string  $caption
	 * @return static
	 */
	public function setCaption($caption)
	{
		$this->caption = $caption;
		return $this;
	}


	/** @return object|string */
	public function getCaption()
	{
		return $this->caption;
	}


	/**
	 * Returns form.
	 */
	public function getForm(bool $throw = true): ?Form
	{
		return $this->lookup(Form::class, $throw);
	}


	/**
	 * Loads HTTP data.
	 */
	public function loadHttpData(): void
	{
		$this->setValue($this->getHttpData(Form::DATA_TEXT));
	}


	/**
	 * Loads HTTP data.
	 * @return mixed
	 */
	protected function getHttpData($type, string $htmlTail = null)
	{
		return $this->getForm()->getHttpData($type, $this->getHtmlName() . $htmlTail);
	}


	/**
	 * Returns HTML name of control.
	 */
	public function getHtmlName(): string
	{
		return $this->control->name ?? Nette\Forms\Helpers::generateHtmlName($this->lookupPath(Form::class));
	}


	/********************* interface IControl ****************d*g**/


	/**
	 * Sets control's value.
	 * @return static
	 * @internal
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}


	/**
	 * Returns control's value.
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * Is control filled?
	 */
	public function isFilled(): bool
	{
		$value = $this->getValue();
		return $value !== null && $value !== [] && $value !== '';
	}


	/**
	 * Sets control's default value.
	 * @return static
	 */
	public function setDefaultValue($value)
	{
		$form = $this->getForm(false);
		if ($this->isDisabled() || !$form || !$form->isAnchored() || !$form->isSubmitted()) {
			$this->setValue($value);
		}
		return $this;
	}


	/**
	 * Disables or enables control.
	 * @return static
	 */
	public function setDisabled(/*bool*/ $value = true)
	{
		if ($this->disabled = (bool) $value) {
			$this->setValue(null);
		} elseif (($form = $this->getForm(false)) && $form->isAnchored() && $form->isSubmitted()) {
			$this->loadHttpData();
		}
		return $this;
	}


	/**
	 * Is control disabled?
	 */
	public function isDisabled(): bool
	{
		return $this->disabled === true;
	}


	/**
	 * Sets whether control value is excluded from $form->getValues() result.
	 * @return static
	 */
	public function setOmitted(bool $value = true)
	{
		$this->omitted = $value;
		return $this;
	}


	/**
	 * Is control value excluded from $form->getValues() result?
	 */
	public function isOmitted(): bool
	{
		return $this->omitted || ($this->isDisabled() && $this->omitted === null);
	}


	/********************* rendering ****************d*g**/


	/**
	 * Generates control's HTML element.
	 * @return Html|string
	 */
	public function getControl()
	{
		$this->setOption('rendered', true);
		$el = clone $this->control;
		return $el->addAttributes([
			'name' => $this->getHtmlName(),
			'id' => $this->getHtmlId(),
			'required' => $this->isRequired(),
			'disabled' => $this->isDisabled(),
			'data-nette-rules' => Nette\Forms\Helpers::exportRules($this->rules) ?: null,
		]);
	}


	/**
	 * Generates label's HTML element.
	 * @param  string|object  $caption
	 * @return Html|string|null
	 */
	public function getLabel($caption = null)
	{
		$label = clone $this->label;
		$label->for = $this->getHtmlId();
		$caption = $caption === null ? $this->caption : $caption;
		$translator = $this->getForm()->getTranslator();
		$label->setText($translator && !$caption instanceof Nette\Utils\IHtmlString ? $translator->translate($caption) : $caption);
		return $label;
	}


	public function getControlPart(): ?Html
	{
		return $this->getControl();
	}


	public function getLabelPart(): ?Html
	{
		return $this->getLabel();
	}


	/**
	 * Returns control's HTML element template.
	 */
	public function getControlPrototype(): Html
	{
		return $this->control;
	}


	/**
	 * Returns label's HTML element template.
	 */
	public function getLabelPrototype(): Html
	{
		return $this->label;
	}


	/**
	 * Changes control's HTML id.
	 * @param  string|bool|null  $id
	 * @return static
	 */
	public function setHtmlId($id)
	{
		$this->control->id = $id;
		return $this;
	}


	/**
	 * Returns control's HTML id.
	 * @return mixed
	 */
	public function getHtmlId()
	{
		if (!isset($this->control->id)) {
			$form = $this->getForm();
			$prefix = $form instanceof Nette\Application\UI\Form || $form->getName() === null
				? ''
				: $form->getName() . '-';
			$this->control->id = sprintf(self::$idMask, $prefix . $this->lookupPath());
		}
		return $this->control->id;
	}


	/**
	 * Changes control's HTML attribute.
	 * @return static
	 */
	public function setHtmlAttribute(string $name, $value = true)
	{
		$this->control->$name = $value;
		if ($name === 'name' && ($form = $this->getForm(false)) && !$this->isDisabled() && $form->isAnchored() && $form->isSubmitted()) {
			$this->loadHttpData();
		}
		return $this;
	}


	/**
	 * @deprecated  use setHtmlAttribute()
	 * @return static
	 */
	public function setAttribute(string $name, $value = true)
	{
		return $this->setHtmlAttribute($name, $value);
	}


	/********************* translator ****************d*g**/


	/**
	 * Sets translate adapter.
	 * @return static
	 */
	public function setTranslator(?Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;
		return $this;
	}


	/**
	 * Returns translate adapter.
	 */
	public function getTranslator(): ?Nette\Localization\ITranslator
	{
		if ($this->translator === true) {
			return $this->getForm(false) ? $this->getForm()->getTranslator() : null;
		}
		return $this->translator;
	}


	/**
	 * Returns translated string.
	 * @return mixed
	 */
	public function translate($value, ...$parameters)
	{
		if ($translator = $this->getTranslator()) {
			$tmp = is_array($value) ? [&$value] : [[&$value]];
			foreach ($tmp[0] as &$v) {
				if ($v != null && !$v instanceof Nette\Utils\IHtmlString) { // intentionally ==
					$v = $translator->translate($v, ...$parameters);
				}
			}
		}
		return $value;
	}


	/********************* rules ****************d*g**/


	/**
	 * Adds a validation rule.
	 * @param  callable|string  $validator
	 * @param  string|object  $errorMessage
	 * @return static
	 */
	public function addRule($validator, $errorMessage = null, $arg = null)
	{
		$this->rules->addRule($validator, $errorMessage, $arg);
		return $this;
	}


	/**
	 * Adds a validation condition a returns new branch.
	 * @return Rules      new branch
	 */
	public function addCondition($validator, $value = null): Rules
	{
		return $this->rules->addCondition($validator, $value);
	}


	/**
	 * Adds a validation condition based on another control a returns new branch.
	 * @return Rules      new branch
	 */
	public function addConditionOn(IControl $control, $validator, $value = null): Rules
	{
		return $this->rules->addConditionOn($control, $validator, $value);
	}


	public function getRules(): Rules
	{
		return $this->rules;
	}


	/**
	 * Makes control mandatory.
	 * @param  bool|string|object  $value
	 * @return static
	 */
	public function setRequired($value = true)
	{
		$this->rules->setRequired($value);
		return $this;
	}


	/**
	 * Is control mandatory?
	 */
	public function isRequired(): bool
	{
		return $this->rules->isRequired();
	}


	/**
	 * Performs the server side validation.
	 */
	public function validate(): void
	{
		if ($this->isDisabled()) {
			return;
		}
		$this->cleanErrors();
		$this->rules->validate();
	}


	/**
	 * Adds error message to the list.
	 * @param  string|object  $message
	 */
	public function addError($message, bool $translate = true): void
	{
		$this->errors[] = $translate ? $this->translate($message) : $message;
	}


	/**
	 * Returns errors corresponding to control.
	 */
	public function getError(): ?string
	{
		return $this->errors ? implode(' ', array_unique($this->errors)) : null;
	}


	/**
	 * Returns errors corresponding to control.
	 */
	public function getErrors(): array
	{
		return array_unique($this->errors);
	}


	public function hasErrors(): bool
	{
		return (bool) $this->errors;
	}


	public function cleanErrors(): void
	{
		$this->errors = [];
	}


	/** @deprecated */
	public static function enableAutoOptionalMode(): void
	{
		trigger_error(__METHOD__ . '() is deprecated.', E_USER_DEPRECATED);
	}


	/********************* user data ****************d*g**/


	/**
	 * Sets user-specific option.
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
	 * @return mixed
	 */
	public function getOption($key, $default = null)
	{
		return $this->options[$key] ?? $default;
	}


	/**
	 * Returns user-specific options.
	 */
	public function getOptions(): array
	{
		return $this->options;
	}


	/********************* extension methods ****************d*g**/


	public function __call(string $name, array $args)
	{
		$class = static::class;
		do {
			if (isset(self::$extMethods[$name][$class])) {
				return (self::$extMethods[$name][$class])($this, ...$args);
			}
			$class = get_parent_class($class);
		} while ($class);
		return parent::__call($name, $args);
	}


	public static function extensionMethod(string $name, /*callable*/ $callback): void
	{
		if (strpos($name, '::') !== false) { // back compatibility
			[, $name] = explode('::', $name);
		}
		self::$extMethods[$name][static::class] = $callback;
	}
}
