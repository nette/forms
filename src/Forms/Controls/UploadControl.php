<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms;
use Nette\Forms\{Form, Validation};
use Nette\Http\FileUpload;
use Nette\Utils\Arrays;
use Stringable;
use function ini_get, is_array, is_int;


/**
 * Text box and browse button that allow users to select a file to upload to the server.
 */
class UploadControl extends BaseControl
{
	/** validation message keys */
	public const Valid = 'forms.upload';
	public const MaxFiles = 'forms.upload.maxFiles';

	#[\Deprecated('use UploadControl::Valid')]
	public const VALID = self::Valid;

	private bool $nullable = false;


	public function __construct(string|Stringable|null $label = null, bool $multiple = false)
	{
		parent::__construct($label);
		$this->control->type = 'file';
		$this->control->multiple = $multiple;
		$this->setOption('type', 'file');
		$this->addCondition(true) // not to block the export of rules to JS
			->addRule($this->isOk(...), new Validation\Message(self::Valid, 'An error occurred during file upload.'));
		$this->addRule(Form::MaxFileSize, null, Forms\Helpers::iniGetSize('upload_max_filesize'));
		if ($multiple) {
			$maxFiles = (int) ini_get('max_file_uploads');
			$this->addRule(
				Form::MaxLength,
				new Validation\Message(self::MaxFiles, 'The maximum allowed number of uploaded files is %count.', count: $maxFiles),
				$maxFiles,
			);
		}

		$this->monitor(Form::class, function (Form $form): void {
			if (!$form->isMethod('post')) {
				throw new Nette\InvalidStateException('File upload requires method POST.');
			}

			$form->getElementPrototype()->enctype = 'multipart/form-data';
		});
	}


	public function loadHttpData(): void
	{
		$this->value = $this->getSubmittedValue($this->control->multiple ? Form::DataList | Form::DataFile : Form::DataFile);
	}


	public function getHtmlName(): string
	{
		return parent::getHtmlName() . ($this->control->multiple ? '[]' : '');
	}


	/** @internal */
	public function setValue(mixed $value): static
	{
		return $this;
	}


	/**
	 * Returns the uploaded file(s), a dummy FileUpload(null) when nothing was uploaded, or null when nullable is set.
	 * @return FileUpload|FileUpload[]|null
	 */
	public function getValue(): FileUpload|array|null
	{
		return $this->value ?? ($this->nullable ? null : new FileUpload(null));
	}


	/**
	 * Has been any file uploaded?
	 */
	public function isFilled(): bool
	{
		return (bool) $this->value;
	}


	/**
	 * Sets whether getValue() returns null instead of FileUpload with error UPLOAD_ERR_NO_FILE.
	 */
	public function setNullable(bool $value = true): static
	{
		$this->nullable = $value;
		return $this;
	}


	public function isNullable(): bool
	{
		return $this->nullable;
	}


	/**
	 * Have been all files successfully uploaded?
	 * @internal
	 */
	public function isOk(): bool
	{
		return match (true) {
			!$this->value => false,
			is_array($this->value) => Arrays::every($this->value, fn(FileUpload $upload): bool => $upload->isOk()),
			default => $this->value->isOk(),
		};
	}


	/** @param  Validation\Validator|(callable(never, never): bool)|string  $validator */
	public function addRule(
		Validation\Validator|callable|string $validator,
		string|Stringable|null $errorMessage = null,
		mixed $arg = null,
	): static
	{
		if ($validator === Form::MaxFileSize || $validator instanceof Validation\FileSizeValidator) {
			$limit = $validator instanceof Validation\FileSizeValidator ? $validator->limit : $arg;
			if (is_int($limit) && $limit > ($ini = Forms\Helpers::iniGetSize('upload_max_filesize'))) {
				trigger_error(
					"Value of MaxFileSize ($limit) is greater than value of directive upload_max_filesize ($ini).",
					E_USER_WARNING,
				);
			}
			$this->getRules()->removeRule(Form::MaxFileSize);
			$this->getRules()->removeRule(Validation\FileSizeValidator::class);

		} elseif ($validator === Form::MaxLength) {
			if ($arg > ($ini = ini_get('max_file_uploads'))) {
				trigger_error("Value of MaxLength ($arg) is greater than value of directive max_file_uploads ($ini).", E_USER_WARNING);
			}
			$this->getRules()->removeRule($validator);
		}

		return parent::addRule($validator, $errorMessage, $arg);
	}


	protected function getRuleAttributeNames(): array
	{
		return ['accept'];
	}
}
