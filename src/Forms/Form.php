<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms;

use Nette;
use Nette\Utils\Arrays;
use Nette\Utils\Html;


/**
 * Creates, validates and renders HTML forms.
 *
 * @property-read array $errors
 * @property-read array $ownErrors
 * @property-read Html $elementPrototype
 * @property-read FormRenderer $renderer
 * @property string $action
 * @property string $method
 */
class Form extends Container implements Nette\HtmlStringable
{
	/** validator */
	public const
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
		PATTERN_ICASE = ':patternCaseInsensitive',
		INTEGER = ':integer',
		NUMERIC = ':numeric',
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

	/** method */
	public const
		GET = 'get',
		POST = 'post';

	/** submitted data types */
	public const
		DATA_TEXT = 1,
		DATA_LINE = 2,
		DATA_FILE = 3,
		DATA_KEYS = 8;

	/** @internal tracker ID */
	public const TRACKER_ID = '_form_';

	/** @internal protection token ID */
	public const PROTECTOR_ID = '_token_';

	/**
	 * Occurs when the form is submitted and successfully validated
	 * @var array<callable(self, array|object): void|callable(array|object): void>
	 */
	public iterable $onSuccess = [];

	/** @var array<callable(self): void>  Occurs when the form is submitted and is not valid */
	public iterable $onError = [];

	/** @var array<callable(self): void>  Occurs when the form is submitted */
	public iterable $onSubmit = [];

	/** @var array<callable(self): void>  Occurs before the form is rendered */
	public iterable $onRender = [];

	/** @internal used only by standalone form */
	public Nette\Http\IRequest $httpRequest;

	protected bool $crossOrigin = false;

	private static ?Nette\Http\IRequest $defaultHttpRequest = null;

	private SubmitterControl|bool $submittedBy;

	private array $httpData;

	/** element <form> */
	private Html $element;

	private FormRenderer $renderer;

	private ?Nette\Localization\Translator $translator = null;

	/** @var ControlGroup[] */
	private array $groups = [];

	private array $errors = [];

	private bool $beforeRenderCalled = false;


	public function __construct(?string $name = null)
	{
		if ($name !== null) {
			$this->getElementPrototype()->id = 'frm-' . $name;
			$tracker = new Controls\HiddenField($name);
			$tracker->setOmitted();
			$this[self::TRACKER_ID] = $tracker;
			$this->setParent(null, $name);
		}

		$this->monitor(self::class, function (): void {
			throw new Nette\InvalidStateException('Nested forms are forbidden.');
		});
	}


	/**
	 * Returns self.
	 */
	public function getForm(bool $throw = true): static
	{
		return $this;
	}


	/**
	 * Sets form's action.
	 * @param  string|object  $url
	 */
	public function setAction($url): static
	{
		$this->getElementPrototype()->action = $url;
		return $this;
	}


	/**
	 * Returns form's action.
	 */
	public function getAction(): mixed
	{
		return $this->getElementPrototype()->action;
	}


	/**
	 * Sets form's method GET or POST.
	 */
	public function setMethod(string $method): static
	{
		if (isset($this->httpData)) {
			throw new Nette\InvalidStateException(__METHOD__ . '() must be called until the form is empty.');
		}

		$this->getElementPrototype()->method = strtolower($method);
		return $this;
	}


	/**
	 * Returns form's method.
	 */
	public function getMethod(): string
	{
		return $this->getElementPrototype()->method;
	}


	/**
	 * Checks if the request method is the given one.
	 */
	public function isMethod(string $method): bool
	{
		return strcasecmp($this->getElementPrototype()->method, $method) === 0;
	}


	/**
	 * Changes forms's HTML attribute.
	 */
	public function setHtmlAttribute(string $name, mixed $value = true): static
	{
		$this->getElementPrototype()->$name = $value;
		return $this;
	}


	/**
	 * Disables CSRF protection using a SameSite cookie.
	 */
	public function allowCrossOrigin(): void
	{
		$this->crossOrigin = true;
	}


	/**
	 * Cross-Site Request Forgery (CSRF) form protection.
	 */
	public function addProtection(?string $errorMessage = null): Controls\CsrfProtection
	{
		$control = new Controls\CsrfProtection($errorMessage);
		$this->addComponent($control, self::PROTECTOR_ID, key((array) $this->getComponents()));
		return $control;
	}


	/**
	 * Adds fieldset group to the form.
	 */
	public function addGroup(string|object|null $caption = null, bool $setAsCurrent = true): ControlGroup
	{
		$group = new ControlGroup;
		$group->setOption('label', $caption);
		$group->setOption('visual', true);

		if ($setAsCurrent) {
			$this->setCurrentGroup($group);
		}

		return !is_scalar($caption) || isset($this->groups[$caption])
			? $this->groups[] = $group
			: $this->groups[$caption] = $group;
	}


	/**
	 * Removes fieldset group from form.
	 */
	public function removeGroup(string|ControlGroup $name): void
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
	public function getGroups(): array
	{
		return $this->groups;
	}


	/**
	 * Returns the specified group.
	 */
	public function getGroup(string|int $name): ?ControlGroup
	{
		return $this->groups[$name] ?? null;
	}


	/********************* translator ****************d*g**/


	/**
	 * Sets translate adapter.
	 */
	public function setTranslator(?Nette\Localization\Translator $translator): static
	{
		$this->translator = $translator;
		return $this;
	}


	/**
	 * Returns translate adapter.
	 */
	public function getTranslator(): ?Nette\Localization\Translator
	{
		return $this->translator;
	}


	/********************* submission ****************d*g**/


	/**
	 * Tells if the form is anchored.
	 */
	public function isAnchored(): bool
	{
		return true;
	}


	/**
	 * Tells if the form was submitted.
	 */
	public function isSubmitted(): SubmitterControl|bool
	{
		if (!isset($this->httpData)) {
			$this->getHttpData();
		}

		return $this->submittedBy;
	}


	/**
	 * Tells if the form was submitted and successfully validated.
	 */
	public function isSuccess(): bool
	{
		return $this->isSubmitted() && $this->isValid();
	}


	/**
	 * Sets the submittor control.
	 * @internal
	 */
	public function setSubmittedBy(?SubmitterControl $by): static
	{
		$this->submittedBy = $by ?? false;
		return $this;
	}


	/**
	 * Returns submitted HTTP data.
	 */
	public function getHttpData(?int $type = null, ?string $htmlName = null): mixed
	{
		if (!isset($this->httpData)) {
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
	 */
	public function fireEvents(): void
	{
		if (!$this->isSubmitted()) {
			return;

		} elseif (!$this->getErrors()) {
			$this->validate();
		}

		$handled = count($this->onSuccess ?? []) || count($this->onSubmit ?? []);

		if ($this->submittedBy instanceof Controls\SubmitButton) {
			$handled = $handled || count($this->submittedBy->onClick ?? []);
			if ($this->isValid()) {
				$this->invokeHandlers($this->submittedBy->onClick, $this->submittedBy);
			} else {
				Arrays::invoke($this->submittedBy->onInvalidClick, $this->submittedBy);
			}
		}

		if ($this->isValid()) {
			$this->invokeHandlers($this->onSuccess);
		}

		if (!$this->isValid()) {
			Arrays::invoke($this->onError, $this);
		}

		Arrays::invoke($this->onSubmit, $this);

		if (!$handled) {
			trigger_error("Form was submitted but there are no associated handlers (form '{$this->getName()}').", E_USER_WARNING);
		}
	}


	private function invokeHandlers(iterable $handlers, $button = null): void
	{
		foreach ($handlers as $handler) {
			$params = Nette\Utils\Callback::toReflection($handler)->getParameters();
			$types = array_map([Nette\Utils\Type::class, 'fromReflection'], $params);
			if (!isset($types[0])) {
				$arg0 = $button ?: $this;
			} elseif ($types[0]->allows($this::class)) {
				$arg0 = $this;
			} elseif ($button && $types[0]->allows($button::class)) {
				$arg0 = $button;
			} else {
				$arg0 = $this->getValues($types[0]->getSingleName());
			}

			$arg1 = isset($params[1])
				? $this->getValues($types[1]?->getSingleName())
				: null;
			$handler($arg0, $arg1);

			if (!$this->isValid()) {
				return;
			}
		}
	}


	/**
	 * Resets form.
	 */
	public function reset(): static
	{
		$this->setSubmittedBy(null);
		$this->setValues([], true);
		return $this;
	}


	/**
	 * Internal: returns submitted HTTP data or null when form was not submitted.
	 */
	protected function receiveHttpData(): ?array
	{
		$httpRequest = $this->getHttpRequest();
		if (strcasecmp($this->getMethod(), $httpRequest->getMethod())) {
			return null;
		}

		if ($httpRequest->isMethod('post')) {
			if (!$this->crossOrigin && !$httpRequest->isSameSite()) {
				return null;
			}

			$data = Nette\Utils\Arrays::mergeTree($httpRequest->getPost(), $httpRequest->getFiles());
		} else {
			$data = $httpRequest->getQuery();
			if (!$data) {
				return null;
			}
		}

		if ($tracker = $this->getComponent(self::TRACKER_ID, false)) {
			if (!isset($data[self::TRACKER_ID]) || $data[self::TRACKER_ID] !== $tracker->getValue()) {
				return null;
			}
		}

		return $data;
	}


	/********************* validation ****************d*g**/


	public function validate(?array $controls = null): void
	{
		$this->cleanErrors();
		if ($controls === null && $this->submittedBy instanceof SubmitterControl) {
			$controls = $this->submittedBy->getValidationScope();
		}

		$this->validateMaxPostSize();
		parent::validate($controls);
	}


	/** @internal */
	public function validateMaxPostSize(): void
	{
		if (!$this->submittedBy || !$this->isMethod('post') || empty($_SERVER['CONTENT_LENGTH'])) {
			return;
		}

		$maxSize = Helpers::iniGetSize('post_max_size');
		if ($maxSize > 0 && $maxSize < $_SERVER['CONTENT_LENGTH']) {
			$this->addError(sprintf(Validator::$messages[self::MAX_FILE_SIZE], $maxSize));
		}
	}


	/**
	 * Adds global error message.
	 * @param  string|object  $message
	 */
	public function addError($message, bool $translate = true): void
	{
		if ($translate && $this->translator) {
			$message = $this->translator->translate($message);
		}

		$this->errors[] = $message;
	}


	/**
	 * Returns global validation errors.
	 */
	public function getErrors(): array
	{
		return array_unique(array_merge($this->errors, parent::getErrors()));
	}


	public function hasErrors(): bool
	{
		return (bool) $this->getErrors();
	}


	public function cleanErrors(): void
	{
		$this->errors = [];
	}


	/**
	 * Returns form's validation errors.
	 */
	public function getOwnErrors(): array
	{
		return array_unique($this->errors);
	}


	/********************* rendering ****************d*g**/


	/**
	 * Returns form's HTML element template.
	 */
	public function getElementPrototype(): Html
	{
		if (!isset($this->element)) {
			$this->element = Html::el('form');
			$this->element->action = ''; // RFC 1808 -> empty uri means 'this'
			$this->element->method = self::POST;
		}

		return $this->element;
	}


	/**
	 * Sets form renderer.
	 */
	public function setRenderer(?FormRenderer $renderer): static
	{
		$this->renderer = $renderer;
		return $this;
	}


	/**
	 * Returns form renderer.
	 */
	public function getRenderer(): FormRenderer
	{
		if (!isset($this->renderer)) {
			$this->renderer = new Rendering\DefaultFormRenderer;
		}

		return $this->renderer;
	}


	protected function beforeRender()
	{
	}


	/**
	 * Must be called before form is rendered and render() is not used.
	 */
	public function fireRenderEvents(): void
	{
		if (!$this->beforeRenderCalled) {
			$this->beforeRenderCalled = true;
			$this->beforeRender();
			Arrays::invoke($this->onRender, $this);
		}
	}


	/**
	 * Renders form.
	 */
	public function render(...$args): void
	{
		$this->fireRenderEvents();
		echo $this->getRenderer()->render($this, ...$args);
	}


	/**
	 * Renders form to string.
	 */
	public function __toString(): string
	{
		$this->fireRenderEvents();
		return $this->getRenderer()->render($this);
	}


	public function getToggles(): array
	{
		$toggles = [];
		foreach ($this->getComponents(true, Controls\BaseControl::class) as $control) {
			$toggles = $control->getRules()->getToggleStates($toggles);
		}

		return $toggles;
	}


	/********************* backend ****************d*g**/


	/**
	 * Initialize standalone forms.
	 */
	public static function initialize(bool $reinit = false): void
	{
		if ($reinit) {
			self::$defaultHttpRequest = null;
			return;
		} elseif (self::$defaultHttpRequest) {
			return;
		}

		self::$defaultHttpRequest = (new Nette\Http\RequestFactory)->fromGlobals();

		if (PHP_SAPI !== 'cli') {
			if (headers_sent($file, $line)) {
				throw new Nette\InvalidStateException(
					'Create a form or call Nette\Forms\Form::initialize() before the headers are sent to initialize CSRF protection.'
					. ($file ? " (output started at $file:$line)" : '') . '. ',
				);
			}

			Nette\Http\Helpers::initCookie(self::$defaultHttpRequest, new Nette\Http\Response);
		}
	}


	private function getHttpRequest(): Nette\Http\IRequest
	{
		if (!isset($this->httpRequest)) {
			self::initialize();
			$this->httpRequest = self::$defaultHttpRequest;
		}

		return $this->httpRequest;
	}
}
