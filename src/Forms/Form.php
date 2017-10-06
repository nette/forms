<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms;

use Nette;
use Nette\Utils\Html;


/**
 * Creates, validates and renders HTML forms.
 *
 * @property-read array $errors
 * @property-read array $ownErrors
 * @property-read Html $elementPrototype
 * @property-read IFormRenderer $renderer
 * @property string $action
 * @property string $method
 */
class Form extends Container implements Nette\Utils\IHtmlString
{
	/** validator */
	const
		EQUAL = ':equal',
		IS_IN = self::EQUAL,
		NOT_EQUAL = ':notEqual',
		IS_NOT_IN = self::NOT_EQUAL,
		FILLED = ':filled',
		BLANK = ':blank',
		REQUIRED = self::FILLED,
		VALID = ':valid',

		// button
		SUBMITTED = ':submitted',

		// text
		MIN_LENGTH = ':minLength',
		MAX_LENGTH = ':maxLength',
		LENGTH = ':length',
		EMAIL = ':email',
		URL = ':url',
		PATTERN = ':pattern',
		INTEGER = ':integer',
		NUMERIC = ':integer',
		FLOAT = ':float',
		MIN = ':min',
		MAX = ':max',
		RANGE = ':range',

		// multiselect
		COUNT = self::LENGTH,

		// file upload
		MAX_FILE_SIZE = ':fileSize',
		MIME_TYPE = ':mimeType',
		IMAGE = ':image',
		MAX_POST_SIZE = ':maxPostSize';

	/** @deprecated CSRF protection */
	const PROTECTION = Controls\CsrfProtection::PROTECTION;

	/** method */
	const
		GET = 'get',
		POST = 'post';

	/** submitted data types */
	const
		DATA_TEXT = 1,
		DATA_LINE = 2,
		DATA_FILE = 3,
		DATA_KEYS = 8;

	/** @internal tracker ID */
	const TRACKER_ID = '_form_';

	/** @internal protection token ID */
	const PROTECTOR_ID = '_token_';

	/** @var callable[]  function (Form $sender); Occurs when the form is submitted and successfully validated */
	public $onSuccess;

	/** @var callable[]  function (Form $sender); Occurs when the form is submitted and is not valid */
	public $onError;

	/** @var callable[]  function (Form $sender); Occurs when the form is submitted */
	public $onSubmit;

	/** @var callable[]  function (Form $sender); Occurs before the form is rendered */
	public $onRender;

	/** @var Nette\Http\IRequest  used only by standalone form */
	public $httpRequest;

	/** @var mixed or null meaning: not detected yet */
	private $submittedBy;

	/** @var array */
	private $httpData;

	/** @var Html  <form> element */
	private $element;

	/** @var IFormRenderer */
	private $renderer;

	/** @var Nette\Localization\ITranslator */
	private $translator;

	/** @var ControlGroup[] */
	private $groups = [];

	/** @var array */
	private $errors = [];

	/** @var bool */
	private $beforeRenderCalled;


	/**
	 * Form constructor.
	 * @param  string
	 */
	public function __construct($name = null)
	{
		parent::__construct();
		if ($name !== null) {
			$this->getElementPrototype()->id = 'frm-' . $name;
			$tracker = new Controls\HiddenField($name);
			$tracker->setOmitted();
			$this[self::TRACKER_ID] = $tracker;
			$this->setParent(null, $name);
		}
	}


	/**
	 * @return void
	 */
	protected function validateParent(Nette\ComponentModel\IContainer $parent)
	{
		parent::validateParent($parent);
		$this->monitor(__CLASS__);
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($obj)
	{
		if ($obj instanceof self) {
			throw new Nette\InvalidStateException('Nested forms are forbidden.');
		}
	}


	/**
	 * Returns self.
	 * @return static
	 */
	public function getForm($throw = true)
	{
		return $this;
	}


	/**
	 * Sets form's action.
	 * @param  string|object
	 * @return static
	 */
	public function setAction($url)
	{
		$this->getElementPrototype()->action = $url;
		return $this;
	}


	/**
	 * Returns form's action.
	 * @return mixed
	 */
	public function getAction()
	{
		return $this->getElementPrototype()->action;
	}


	/**
	 * Sets form's method GET or POST.
	 * @param  string
	 * @return static
	 */
	public function setMethod($method)
	{
		if ($this->httpData !== null) {
			throw new Nette\InvalidStateException(__METHOD__ . '() must be called until the form is empty.');
		}
		$this->getElementPrototype()->method = strtolower($method);
		return $this;
	}


	/**
	 * Returns form's method.
	 * @return string
	 */
	public function getMethod()
	{
		return $this->getElementPrototype()->method;
	}


	/**
	 * Checks if the request method is the given one.
	 * @param  string
	 * @return bool
	 */
	public function isMethod($method)
	{
		return strcasecmp($this->getElementPrototype()->method, $method) === 0;
	}


	/**
	 * Cross-Site Request Forgery (CSRF) form protection.
	 * @param  string
	 * @return Controls\CsrfProtection
	 */
	public function addProtection($errorMessage = null)
	{
		$control = new Controls\CsrfProtection($errorMessage);
		$this->addComponent($control, self::PROTECTOR_ID, key($this->getComponents()));
		return $control;
	}


	/**
	 * Adds fieldset group to the form.
	 * @param  string
	 * @param  bool
	 * @return ControlGroup
	 */
	public function addGroup($caption = null, $setAsCurrent = true)
	{
		$group = new ControlGroup;
		$group->setOption('label', $caption);
		$group->setOption('visual', true);

		if ($setAsCurrent) {
			$this->setCurrentGroup($group);
		}

		if (!is_scalar($caption) || isset($this->groups[$caption])) {
			return $this->groups[] = $group;
		} else {
			return $this->groups[$caption] = $group;
		}
	}


	/**
	 * Removes fieldset group from form.
	 * @param  string|int|ControlGroup
	 * @return void
	 */
	public function removeGroup($name)
	{
		if (is_string($name) && isset($this->groups[$name])) {
			$group = $this->groups[$name];

		} elseif ($name instanceof ControlGroup && in_array($name, $this->groups, true)) {
			$group = $name;
			$name = array_search($group, $this->groups, true);

		} else {
			throw new Nette\InvalidArgumentException("Group not found in form '$this->name'");
		}

		foreach ($group->getControls() as $control) {
			$control->getParent()->removeComponent($control);
		}

		unset($this->groups[$name]);
	}


	/**
	 * Returns all defined groups.
	 * @return ControlGroup[]
	 */
	public function getGroups()
	{
		return $this->groups;
	}


	/**
	 * Returns the specified group.
	 * @param  string|int
	 * @return ControlGroup|null
	 */
	public function getGroup($name)
	{
		return isset($this->groups[$name]) ? $this->groups[$name] : null;
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
		return $this->translator;
	}


	/********************* submission ****************d*g**/


	/**
	 * Tells if the form is anchored.
	 * @return bool
	 */
	public function isAnchored()
	{
		return true;
	}


	/**
	 * Tells if the form was submitted.
	 * @return ISubmitterControl|bool  submittor control
	 */
	public function isSubmitted()
	{
		if ($this->submittedBy === null) {
			$this->getHttpData();
		}
		return $this->submittedBy;
	}


	/**
	 * Tells if the form was submitted and successfully validated.
	 * @return bool
	 */
	public function isSuccess()
	{
		return $this->isSubmitted() && $this->isValid();
	}


	/**
	 * Sets the submittor control.
	 * @return static
	 * @internal
	 */
	public function setSubmittedBy(ISubmitterControl $by = null)
	{
		$this->submittedBy = $by === null ? false : $by;
		return $this;
	}


	/**
	 * Returns submitted HTTP data.
	 * @param  int
	 * @param  string
	 * @return mixed
	 */
	public function getHttpData($type = null, $htmlName = null)
	{
		if ($this->httpData === null) {
			if (!$this->isAnchored()) {
				throw new Nette\InvalidStateException('Form is not anchored and therefore can not determine whether it was submitted.');
			}
			$data = $this->receiveHttpData();
			$this->httpData = (array) $data;
			$this->submittedBy = is_array($data);
		}
		if ($htmlName === null) {
			return $this->httpData;
		}
		return Helpers::extractHttpData($this->httpData, $htmlName, $type);
	}


	/**
	 * Fires submit/click events.
	 * @return void
	 */
	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;

		} elseif (!$this->getErrors()) {
			$this->validate();
		}

		if ($this->submittedBy instanceof ISubmitterControl) {
			if ($this->isValid()) {
				if ($handlers = $this->submittedBy->onClick) {
					if (!is_array($handlers) && !$handlers instanceof \Traversable) {
						throw new Nette\UnexpectedValueException("Property \$onClick in button '{$this->submittedBy->getName()}' must be iterable, " . gettype($handlers) . ' given.');
					}
					$this->invokeHandlers($handlers, $this->submittedBy);
				}
			} else {
				$this->submittedBy->onInvalidClick($this->submittedBy);
			}
		}

		if (!$this->isValid()) {
			$this->onError($this);

		} elseif ($this->onSuccess !== null) {
			if (!is_array($this->onSuccess) && !$this->onSuccess instanceof \Traversable) {
				throw new Nette\UnexpectedValueException('Property Form::$onSuccess must be array or Traversable, ' . gettype($this->onSuccess) . ' given.');
			}
			$this->invokeHandlers($this->onSuccess);
			if (!$this->isValid()) {
				$this->onError($this);
			}
		}

		$this->onSubmit($this);
	}


	private function invokeHandlers($handlers, $button = null)
	{
		foreach ($handlers as $handler) {
			$params = Nette\Utils\Callback::toReflection($handler)->getParameters();
			$values = isset($params[1]) ? $this->getValues($params[1]->isArray()) : null;
			Nette\Utils\Callback::invoke($handler, $button ?: $this, $values);
			if (!$button && !$this->isValid()) {
				return;
			}
		}
	}


	/**
	 * Resets form.
	 * @return static
	 */
	public function reset()
	{
		$this->setSubmittedBy(null);
		$this->setValues([], true);
		return $this;
	}


	/**
	 * Internal: returns submitted HTTP data or null when form was not submitted.
	 * @return array|null
	 */
	protected function receiveHttpData()
	{
		$httpRequest = $this->getHttpRequest();
		if (strcasecmp($this->getMethod(), $httpRequest->getMethod())) {
			return;
		}

		if ($httpRequest->isMethod('post')) {
			$data = Nette\Utils\Arrays::mergeTree($httpRequest->getPost(), $httpRequest->getFiles());
		} else {
			$data = $httpRequest->getQuery();
			if (!$data) {
				return;
			}
		}

		if ($tracker = $this->getComponent(self::TRACKER_ID, false)) {
			if (!isset($data[self::TRACKER_ID]) || $data[self::TRACKER_ID] !== $tracker->getValue()) {
				return;
			}
		}

		return $data;
	}


	/********************* validation ****************d*g**/


	/**
	 * @return void
	 */
	public function validate(array $controls = null)
	{
		$this->cleanErrors();
		if ($controls === null && $this->submittedBy instanceof ISubmitterControl) {
			$controls = $this->submittedBy->getValidationScope();
		}
		$this->validateMaxPostSize();
		parent::validate($controls);
	}


	/** @internal */
	public function validateMaxPostSize()
	{
		if (!$this->submittedBy || !$this->isMethod('post') || empty($_SERVER['CONTENT_LENGTH'])) {
			return;
		}
		$maxSize = ini_get('post_max_size');
		$units = ['k' => 10, 'm' => 20, 'g' => 30];
		if (isset($units[$ch = strtolower(substr($maxSize, -1))])) {
			$maxSize = (int) $maxSize << $units[$ch];
		}
		if ($maxSize > 0 && $maxSize < $_SERVER['CONTENT_LENGTH']) {
			$this->addError(sprintf(Validator::$messages[self::MAX_FILE_SIZE], $maxSize));
		}
	}


	/**
	 * Adds global error message.
	 * @param  string|object
	 * @return void
	 */
	public function addError($message, $translate = true)
	{
		if ($translate && $this->translator) {
			$message = $this->translator->translate($message);
		}
		$this->errors[] = $message;
	}


	/**
	 * Returns global validation errors.
	 * @return array
	 */
	public function getErrors()
	{
		return array_unique(array_merge($this->errors, parent::getErrors()));
	}


	/**
	 * @return bool
	 */
	public function hasErrors()
	{
		return (bool) $this->getErrors();
	}


	/**
	 * @return void
	 */
	public function cleanErrors()
	{
		$this->errors = [];
	}


	/**
	 * Returns form's validation errors.
	 * @return array
	 */
	public function getOwnErrors()
	{
		return array_unique($this->errors);
	}


	/********************* rendering ****************d*g**/


	/**
	 * Returns form's HTML element template.
	 * @return Html
	 */
	public function getElementPrototype()
	{
		if (!$this->element) {
			$this->element = Html::el('form');
			$this->element->action = ''; // RFC 1808 -> empty uri means 'this'
			$this->element->method = self::POST;
		}
		return $this->element;
	}


	/**
	 * Sets form renderer.
	 * @return static
	 */
	public function setRenderer(IFormRenderer $renderer = null)
	{
		$this->renderer = $renderer;
		return $this;
	}


	/**
	 * Returns form renderer.
	 * @return IFormRenderer
	 */
	public function getRenderer()
	{
		if ($this->renderer === null) {
			$this->renderer = new Rendering\DefaultFormRenderer;
		}
		return $this->renderer;
	}


	/**
	 * @return void
	 */
	protected function beforeRender()
	{
	}


	/**
	 * Must be called before form is rendered and render() is not used.
	 * @return void
	 */
	public function fireRenderEvents()
	{
		if (!$this->beforeRenderCalled) {
			foreach ($this->getComponents(true, Controls\BaseControl::class) as $control) {
				$control->getRules()->check();
			}
			$this->beforeRenderCalled = true;
			$this->beforeRender();
			$this->onRender($this);
		}
	}


	/**
	 * Renders form.
	 * @return void
	 */
	public function render(...$args)
	{
		$this->fireRenderEvents();
		echo $this->getRenderer()->render($this, ...$args);
	}


	/**
	 * Renders form to string.
	 * @param can throw exceptions? (hidden parameter)
	 * @return string
	 */
	public function __toString()
	{
		try {
			$this->fireRenderEvents();
			return $this->getRenderer()->render($this);

		} catch (\Exception $e) {
		} catch (\Throwable $e) {
		}
		if (isset($e)) {
			if (func_num_args()) {
				throw $e;
			}
			trigger_error('Exception in ' . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
		}
	}


	/********************* backend ****************d*g**/


	/**
	 * @return Nette\Http\IRequest
	 */
	private function getHttpRequest()
	{
		if (!$this->httpRequest) {
			$factory = new Nette\Http\RequestFactory;
			$this->httpRequest = $factory->createHttpRequest();
		}
		return $this->httpRequest;
	}


	/**
	 * @return array
	 */
	public function getToggles()
	{
		$toggles = [];
		foreach ($this->getComponents(true, Controls\BaseControl::class) as $control) {
			$toggles = $control->getRules()->getToggleStates($toggles);
		}
		return $toggles;
	}
}
