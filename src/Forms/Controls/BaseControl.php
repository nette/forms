<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

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

	/** @var bool */
	protected $disabled = false;

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

	/** @var bool */
	private static $autoOptional = false;


	/**
	 * @param  string|object
	 */
	public function __construct($caption = null)
	{
		$this->monitor(Form::class);
		parent::__construct();
		$this->control = Html::el('input', ['type' => null, 'name' => null]);
		$this->label = Html::el('label');
		$this->caption = $caption;
		$this->rules = new Rules($this);
		if (self::$autoOptional) {
			$this->setRequired(false);
		}
		$this->setValue(null);
	}


	/**
	 * This method will be called when the component becomes attached to Form.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($form)
	{
		if (!$this->isDisabled() && $form instanceof Form && $form->isAnchored() && $form->isSubmitted()) {
			$this->loadHttpData();
		}
	}


	/**
	 * Returns form.
	 * @param  bool
	 * @return Form|null
	 */
	public function getForm($throw = true)
	{
		return $this->lookup(Form::class, $throw);
	}


	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$this->setValue($this->getHttpData(Form::DATA_TEXT));
	}


	/**
	 * Loads HTTP data.
	 * @return mixed
	 */
	protected function getHttpData($type, $htmlTail = null)
	{
		return $this->getForm()->getHttpData($type, $this->getHtmlName() . $htmlTail);
	}


	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName()
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
	 * @return bool
	 */
	public function isFilled()
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
	 * @param  bool
	 * @return static
	 */
	public function setDisabled($value = true)
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
	 * @return bool
	 */
	public function isDisabled()
	{
		return $this->disabled === true;
	}


	/**
	 * Sets whether control value is excluded from $form->getValues() result.
	 * @param  bool
	 * @return static
	 */
	public function setOmitted($value = true)
	{
		$this->omitted = (bool) $value;
		return $this;
	}


	/**
	 * Is control value excluded from $form->getValues() result?
	 * @return bool
	 */
	public function isOmitted()
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
	 * @param  string|object
	 * @return Html|string
	 */
	public function getLabel($caption = null)
	{
		$label = clone $this->label;
		$label->for = $this->getHtmlId();
		$label->setText($this->translate($caption === null ? $this->caption : $caption));
		return $label;
	}


	/**
	 * @return Nette\Utils\Html|null
	 */
	public function getControlPart()
	{
		return $this->getControl();
	}


	/**
	 * @return Nette\Utils\Html|null
	 */
	public function getLabelPart()
	{
		return $this->getLabel();
	}


	/**
	 * Returns control's HTML element template.
	 * @return Html
	 */
	public function getControlPrototype()
	{
		return $this->control;
	}


	/**
	 * Returns label's HTML element template.
	 * @return Html
	 */
	public function getLabelPrototype()
	{
		return $this->label;
	}


	/**
	 * Changes control's HTML id.
	 * @param  mixed  new ID, or false or null
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
	 * @param  string
	 * @param  mixed
	 * @return static
	 */
	public function setHtmlAttribute($name, $value = true)
	{
		return $this->setAttribute($name, $value);
	}


	/**
	 * Alias for setHtmlAttribute()
	 * @param  string
	 * @param  mixed
	 * @return static
	 */
	public function setAttribute($name, $value = true)
	{
		$this->control->$name = $value;
		return $this;
	}


	/********************* translator ****************d*g**/


	/**
	 * Sets translate adapter.
	 * @return static
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator = null)
	{
		$this->translator = $translator;
		return $this;
	}


	/**
	 * Returns translate adapter.
	 * @return Nette\Localization\ITranslator|null
	 */
	public function getTranslator()
	{
		if ($this->translator === true) {
			return $this->getForm(false) ? $this->getForm()->getTranslator() : null;
		}
		return $this->translator;
	}


	/**
	 * Returns translated string.
	 * @param  mixed
	 * @param  int      plural count
	 * @return mixed
	 */
	public function translate($value, $count = null)
	{
		if ($translator = $this->getTranslator()) {
			$tmp = is_array($value) ? [&$value] : [[&$value]];
			foreach ($tmp[0] as &$v) {
				if ($v != null && !$v instanceof Html) { // intentionally ==
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
	 * @param  mixed
	 * @return static
	 */
	public function addRule($validator, $errorMessage = null, $arg = null)
	{
		$this->rules->addRule($validator, $errorMessage, $arg);
		return $this;
	}


	/**
	 * Adds a validation condition a returns new branch.
	 * @param  mixed
	 * @param  mixed
	 * @return Rules      new branch
	 */
	public function addCondition($validator, $value = null)
	{
		return $this->rules->addCondition($validator, $value);
	}


	/**
	 * Adds a validation condition based on another control a returns new branch.
	 * @param  IControl
	 * @param  mixed
	 * @param  mixed
	 * @return Rules      new branch
	 */
	public function addConditionOn(IControl $control, $validator, $value = null)
	{
		return $this->rules->addConditionOn($control, $validator, $value);
	}


	/**
	 * @return Rules
	 */
	public function getRules()
	{
		return $this->rules;
	}


	/**
	 * Makes control mandatory.
	 * @param  mixed  state or error message
	 * @return static
	 */
	public function setRequired($value = true)
	{
		$this->rules->setRequired($value);
		return $this;
	}


	/**
	 * Is control mandatory?
	 * @return bool
	 */
	public function isRequired()
	{
		return $this->rules->isRequired();
	}


	/**
	 * Performs the server side validation.
	 * @return void
	 */
	public function validate()
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
	 * @return void
	 */
	public function addError($message, $translate = true)
	{
		$this->errors[] = $translate ? $this->translate($message) : $message;
	}


	/**
	 * Returns errors corresponding to control.
	 * @return string|null
	 */
	public function getError()
	{
		return $this->errors ? implode(' ', array_unique($this->errors)) : null;
	}


	/**
	 * Returns errors corresponding to control.
	 * @return array
	 */
	public function getErrors()
	{
		return array_unique($this->errors);
	}


	/**
	 * @return bool
	 */
	public function hasErrors()
	{
		return (bool) $this->errors;
	}


	/**
	 * @return void
	 */
	public function cleanErrors()
	{
		$this->errors = [];
	}


	/**
	 * Globally enables new required/optional behavior.
	 * This method will be deprecated in next version.
	 */
	public static function enableAutoOptionalMode()
	{
		self::$autoOptional = true;
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


	/********************* extension methods ****************d*g**/


	public function __call($name, $args)
	{
		if ($callback = Nette\Utils\ObjectMixin::getExtensionMethod(get_class($this), $name)) {
			return Nette\Utils\Callback::invoke($callback, $this, ...$args);
		}
		return parent::__call($name, $args);
	}


	public static function extensionMethod($name, $callback = null)
	{
		if (strpos($name, '::') !== false) { // back compatibility
			list(, $name) = explode('::', $name);
		}
		Nette\Utils\ObjectMixin::setExtensionMethod(get_called_class(), $name, $callback);
	}
}
