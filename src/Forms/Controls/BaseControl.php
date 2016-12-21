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

	/** @var string|object textual caption or label */
	public $caption;

	/** @var mixed current control value */
	protected $value;

	/** @var Html  control element template */
	protected $control;

	/** @var Html  label element template */
	protected $label;

	/** @var array */
	private $errors = [];

	/** @var bool */
	protected $disabled = FALSE;

	/** @var bool|NULL */
	private $omitted;

	/** @var Rules */
	private $rules;

	/** @var Nette\Localization\ITranslator */
	private $translator = TRUE; // means autodetect

	/** @var array user options */
	private $options = [];

	/** @var bool */
	private static $autoOptional = FALSE;


	/**
	 * @param  string|object
	 */
	public function __construct($caption = NULL)
	{
		$this->monitor(Form::class);
		parent::__construct();
		$this->control = Html::el('input', ['type' => NULL, 'name' => NULL]);
		$this->label = Html::el('label');
		$this->caption = $caption;
		$this->rules = new Rules($this);
		if (self::$autoOptional) {
			$this->setRequired(FALSE);
		}
		$this->setValue(NULL);
	}


	/**
	 * This method will be called when the component becomes attached to Form.
	 */
	protected function attached(Nette\ComponentModel\IComponent $form): void
	{
		if (!$this->isDisabled() && $form instanceof Form && $form->isAnchored() && $form->isSubmitted()) {
			$this->loadHttpData();
		}
	}


	/**
	 * Returns form.
	 */
	public function getForm(bool $throw = TRUE): ?Form
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
	protected function getHttpData($type, $htmlTail = NULL)
	{
		return $this->getForm()->getHttpData($type, $this->getHtmlName() . $htmlTail);
	}


	/**
	 * Returns HTML name of control.
	 */
	public function getHtmlName(): string
	{
		return Nette\Forms\Helpers::generateHtmlName($this->lookupPath(Form::class));
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
		return $value !== NULL && $value !== [] && $value !== '';
	}


	/**
	 * Sets control's default value.
	 * @return static
	 */
	public function setDefaultValue($value)
	{
		$form = $this->getForm(FALSE);
		if ($this->isDisabled() || !$form || !$form->isAnchored() || !$form->isSubmitted()) {
			$this->setValue($value);
		}
		return $this;
	}


	/**
	 * Disables or enables control.
	 * @return static
	 */
	public function setDisabled($value = TRUE)
	{
		if ($this->disabled = (bool) $value) {
			$this->setValue(NULL);
		} elseif (($form = $this->getForm(FALSE)) && $form->isAnchored() && $form->isSubmitted()) {
			$this->loadHttpData();
		}
		return $this;
	}


	/**
	 * Is control disabled?
	 */
	public function isDisabled(): bool
	{
		return $this->disabled === TRUE;
	}


	/**
	 * Sets whether control value is excluded from $form->getValues() result.
	 * @return static
	 */
	public function setOmitted(bool $value = TRUE)
	{
		$this->omitted = $value;
		return $this;
	}


	/**
	 * Is control value excluded from $form->getValues() result?
	 */
	public function isOmitted(): bool
	{
		return $this->omitted || ($this->isDisabled() && $this->omitted === NULL);
	}


	/********************* rendering ****************d*g**/


	/**
	 * Generates control's HTML element.
	 * @return Html|string
	 */
	public function getControl()
	{
		$this->setOption('rendered', TRUE);
		$el = clone $this->control;
		return $el->addAttributes([
			'name' => $this->getHtmlName(),
			'id' => $this->getHtmlId(),
			'required' => $this->isRequired(),
			'disabled' => $this->isDisabled(),
			'data-nette-rules' => Nette\Forms\Helpers::exportRules($this->rules) ?: NULL,
		]);
	}


	/**
	 * Generates label's HTML element.
	 * @param  string|object
	 * @return Html|string
	 */
	public function getLabel($caption = NULL)
	{
		$label = clone $this->label;
		$label->for = $this->getHtmlId();
		$label->setText($this->translate($caption === NULL ? $this->caption : $caption));
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
	 * @param  mixed  new ID, or FALSE or NULL
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
			$this->control->id = sprintf(self::$idMask, $this->lookupPath());
		}
		return $this->control->id;
	}


	/**
	 * Changes control's HTML attribute.
	 * @return static
	 */
	public function setHtmlAttribute(string $name, $value = TRUE)
	{
		$this->control->$name = $value;
		return $this;
	}


	/**
	 * @deprecated  use setHtmlAttribute()
	 * @return static
	 */
	public function setAttribute(string $name, $value = TRUE)
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
		if ($this->translator === TRUE) {
			return $this->getForm(FALSE) ? $this->getForm()->getTranslator() : NULL;
		}
		return $this->translator;
	}


	/**
	 * Returns translated string.
	 * @return mixed
	 */
	public function translate($value, int $count = NULL)
	{
		if ($translator = $this->getTranslator()) {
			$tmp = is_array($value) ? [&$value] : [[&$value]];
			foreach ($tmp[0] as &$v) {
				if ($v != NULL && !$v instanceof Html) { // intentionally ==
					$v = $translator->translate($v, $count);
				}
			}
		}
		return $value;
	}


	/********************* rules ****************d*g**/


	/**
	 * Adds a validation rule.
	 * @param  mixed
	 * @param  string|object
	 * @return static
	 */
	public function addRule($validator, $errorMessage = NULL, $arg = NULL)
	{
		$this->rules->addRule($validator, $errorMessage, $arg);
		return $this;
	}


	/**
	 * Adds a validation condition a returns new branch.
	 * @return Rules      new branch
	 */
	public function addCondition($validator, $value = NULL): Rules
	{
		return $this->rules->addCondition($validator, $value);
	}


	/**
	 * Adds a validation condition based on another control a returns new branch.
	 * @return Rules      new branch
	 */
	public function addConditionOn(IControl $control, $validator, $value = NULL): Rules
	{
		return $this->rules->addConditionOn($control, $validator, $value);
	}


	public function getRules(): Rules
	{
		return $this->rules;
	}


	/**
	 * Makes control mandatory.
	 * @param  mixed  state or error message
	 * @return static
	 */
	public function setRequired($value = TRUE)
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
	 * @param  string|object
	 */
	public function addError($message): void
	{
		$this->errors[] = $message;
	}


	/**
	 * Returns errors corresponding to control.
	 */
	public function getError(): ?string
	{
		return $this->errors ? implode(' ', array_unique($this->errors)) : NULL;
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


	/**
	 * Globally enables new required/optional behavior.
	 * This method will be deprecated in next version.
	 */
	public static function enableAutoOptionalMode(): void
	{
		self::$autoOptional = TRUE;
	}


	/********************* user data ****************d*g**/


	/**
	 * Sets user-specific option.
	 * @return static
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
	 * @return mixed
	 */
	public function getOption($key, $default = NULL)
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
		if ($callback = Nette\Utils\ObjectMixin::getExtensionMethod(get_class($this), $name)) {
			return Nette\Utils\Callback::invoke($callback, $this, ...$args);
		}
		return parent::__call($name, $args);
	}


	public static function extensionMethod(string $name, /*callable*/ $callback = NULL): void
	{
		if (strpos($name, '::') !== FALSE) { // back compatibility
			[, $name] = explode('::', $name);
		}
		Nette\Utils\ObjectMixin::setExtensionMethod(get_called_class(), $name, $callback);
	}

}
