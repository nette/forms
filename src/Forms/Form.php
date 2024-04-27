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
use Stringable;


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
		Equal = ':equal',
		IsIn = self::Equal,
		NotEqual = ':notEqual',
		IsNotIn = self::NotEqual,
		Filled = ':filled',
		Blank = ':blank',
		Required = self::Filled,
		Valid = ':valid',

		// button
		Submitted = ':submitted',

		// text
		MinLength = ':minLength',
		MaxLength = ':maxLength',
		Length = ':length',
		Email = ':email',
		URL = ':url',
		Pattern = ':pattern',
		PatternInsensitive = ':patternCaseInsensitive',
		Integer = ':integer',
		Numeric = ':numeric',
		Float = ':float',
		Min = ':min',
		Max = ':max',
		Range = ':range',

		// multiselect
		Count = self::Length,

		// file upload
		MaxFileSize = ':fileSize',
		MimeType = ':mimeType',
		Image = ':image',
		MaxPostSize = ':maxPostSize';

	/** method */
	public const
		Get = 'get',
		Post = 'post';

	/** submitted data types */
	public const
		DataText = 1,
		DataLine = 2,
		DataFile = 3,
		DataKeys = 8;

	/** @internal tracker ID */
	public const TrackerId = '_form_';

	/** @internal protection token ID */
	public const ProtectorId = '_token_';

	/** @deprecated use Form::Equal */
	public const EQUAL = self::Equal;

	/** @deprecated use Form::IsIn */
	public const IS_IN = self::IsIn;

	/** @deprecated use Form::NotEqual */
	public const NOT_EQUAL = self::NotEqual;

	/** @deprecated use Form::IsNotIn */
	public const IS_NOT_IN = self::IsNotIn;

	/** @deprecated use Form::Filled */
	public const FILLED = self::Filled;

	/** @deprecated use Form::Blank */
	public const BLANK = self::Blank;

	/** @deprecated use Form::Required */
	public const REQUIRED = self::Required;

	/** @deprecated use Form::Valid */
	public const VALID = self::Valid;

	/** @deprecated use Form::Submitted */
	public const SUBMITTED = self::Submitted;

	/** @deprecated use Form::MinLength */
	public const MIN_LENGTH = self::MinLength;

	/** @deprecated use Form::MaxLength */
	public const MAX_LENGTH = self::MaxLength;

	/** @deprecated use Form::Length */
	public const LENGTH = self::Length;

	/** @deprecated use Form::Email */
	public const EMAIL = self::Email;

	/** @deprecated use Form::Pattern */
	public const PATTERN = self::Pattern;

	/** @deprecated use Form::PatternCI */
	public const PATTERN_ICASE = self::PatternInsensitive;

	/** @deprecated use Form::Integer */
	public const INTEGER = self::Integer;

	/** @deprecated use Form::Numeric */
	public const NUMERIC = self::Numeric;

	/** @deprecated use Form::Float */
	public const FLOAT = self::Float;

	/** @deprecated use Form::Min */
	public const MIN = self::Min;

	/** @deprecated use Form::Max */
	public const MAX = self::Max;

	/** @deprecated use Form::Range */
	public const RANGE = self::Range;

	/** @deprecated use Form::Count */
	public const COUNT = self::Count;

	/** @deprecated use Form::MaxFileSize */
	public const MAX_FILE_SIZE = self::MaxFileSize;

	/** @deprecated use Form::MimeType */
	public const MIME_TYPE = self::MimeType;

	/** @deprecated use Form::Image */
	public const IMAGE = self::Image;

	/** @deprecated use Form::MaxPostSize */
	public const MAX_POST_SIZE = self::MaxPostSize;

	/** @deprecated use Form::Get */
	public const GET = self::Get;

	/** @deprecated use Form::Post */
	public const POST = self::Post;

	/** @deprecated use Form::DataText */
	public const DATA_TEXT = self::DataText;

	/** @deprecated use Form::DataLine */
	public const DATA_LINE = self::DataLine;

	/** @deprecated use Form::DataFile */
	public const DATA_FILE = self::DataFile;

	/** @deprecated use Form::DataKeys */
	public const DATA_KEYS = self::DataKeys;

	/** @deprecated use Form::TrackerId */
	public const TRACKER_ID = self::TrackerId;

	/** @deprecated use Form::ProtectorId */
	public const PROTECTOR_ID = self::ProtectorId;

	/**
	 * Occurs when the form is submitted and successfully validated
	 * @var array<callable(self, array|object): void|callable(array|object): void>
	 */
	public array $onSuccess = [];

	/** @var array<callable(self): void>  Occurs when the form is submitted and is not valid */
	public array $onError = [];

	/** @var array<callable(self): void>  Occurs when the form is submitted */
	public array $onSubmit = [];

	/** @var array<callable(self): void>  Occurs before the form is rendered */
	public array $onRender = [];

	/** @internal used only by standalone form */
	public Nette\Http\IRequest $httpRequest;

	/** @var bool */
	protected $crossOrigin = false;
	private static ?Nette\Http\IRequest $defaultHttpRequest = null;
	private SubmitterControl|bool $submittedBy = false;
	private array $httpData;
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
			$this[self::TrackerId] = $tracker;
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
	 */
	public function setAction(string|Stringable $url): static
	{
		$this->getElementPrototype()->action = $url;
		return $this;
	}


	/**
	 * Returns form's action.
	 */
	public function getAction(): string|Stringable
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
		$children = $this->getComponents();
		$first = $children ? (string) array_key_first($children) : null;
		$this->addComponent($control, self::ProtectorId, $first);
		return $control;
	}


	/**
	 * Adds fieldset group to the form.
	 */
	public function addGroup(string|Stringable|null $caption = null, bool $setAsCurrent = true): ControlGroup
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

		} elseif ($name instanceof ControlGroup && in_array($name, $this->groups, strict: true)) {
			$group = $name;
			$name = array_search($group, $this->groups, strict: true);

		} else {
			throw new Nette\InvalidArgumentException("Group not found in form '{$this->getName()}'");
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
	public function getHttpData(?int $type = null, ?string $htmlName = null): string|array|Nette\Http\FileUpload|null
	{
		if (!isset($this->httpData)) {
			if (!$this->isAnchored()) {
				throw new Nette\InvalidStateException('Form is not anchored and therefore can not determine whether it was submitted.');
			}

			$data = $this->receiveHttpData();
			$this->httpData = (array) $data;
			$this->submittedBy = is_array($data);
		}

		return $htmlName === null
			? $this->httpData
			: Helpers::extractHttpData($this->httpData, $htmlName, $type);

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
			$types = array_map([Helpers::class, 'getSingleType'], $params);
			if (!isset($types[0])) {
				$arg0 = $button ?: $this;
			} elseif ($this instanceof $types[0]) {
				$arg0 = $this;
			} elseif ($button instanceof $types[0]) {
				$arg0 = $button;
			} else {
				$arg0 = $this->getValues($types[0]);
			}

			$arg1 = isset($params[1]) ? $this->getValues($types[1]) : null;
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
		$this->setValues([], erase: true);
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

		if ($tracker = $this->getComponent(self::TrackerId, throw: false)) {
			if (!isset($data[self::TrackerId]) || $data[self::TrackerId] !== $tracker->getValue()) {
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
			$this->addError(sprintf(Validator::$messages[self::MaxFileSize], $maxSize));
		}
	}


	/**
	 * Adds global error message.
	 */
	public function addError(string|Stringable $message, bool $translate = true): void
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
			$this->element->method = self::Post;
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
		foreach ($this->getComponentTree() as $control) {
			if ($control instanceof Controls\BaseControl) {
				$toggles = $control->getRules()->getToggleStates($toggles);
			}
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

		if (!in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
			if (headers_sent($file, $line)) {
				throw new Nette\InvalidStateException(
					'Create a form or call Nette\Forms\Form::initialize() before the headers are sent to initialize CSRF protection.'
					. ($file ? " (output started at $file:$line)" : '') . '. ',
				);
			}

			$response = new Nette\Http\Response;
			$response->cookieSecure = self::$defaultHttpRequest->isSecured();
			Nette\Http\Helpers::initCookie(self::$defaultHttpRequest, $response);
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
