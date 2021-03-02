<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms;
use Nette\Forms\Form;
use Nette\Http\FileUpload;
use Stringable;


/**
 * Text box and browse button that allow users to select a file to upload to the server.
 */
class UploadControl extends BaseControl
{
	/** validation rule */
	public const VALID = ':uploadControlValid';


	public function __construct(string|Stringable $label = null, bool $multiple = false)
	{
		parent::__construct($label);
		$this->control->type = 'file';
		$this->control->multiple = $multiple;
		$this->setOption('type', 'file');
		$this->addCondition(true) // not to block the export of rules to JS
			->addRule([$this, 'isOk'], Forms\Validator::$messages[self::VALID]);
		$this->addRule(Form::MAX_FILE_SIZE, null, Forms\Helpers::iniGetSize('upload_max_filesize'));

		$this->monitor(Form::class, function (Form $form): void {
			if (!$form->isMethod('post')) {
				throw new Nette\InvalidStateException('File upload requires method POST.');
			}
			$form->getElementPrototype()->enctype = 'multipart/form-data';
		});
	}


	public function loadHttpData(): void
	{
		$this->value = $this->getHttpData(Form::DATA_FILE);
		if ($this->value === null) {
			$this->value = new FileUpload(null);
		}
	}


	public function getHtmlName(): string
	{
		return parent::getHtmlName() . ($this->control->multiple ? '[]' : '');
	}


	/**
	 * @internal
	 */
	public function setValue($value): static
	{
		return $this;
	}


	/**
	 * Has been any file uploaded?
	 */
	public function isFilled(): bool
	{
		return $this->value instanceof FileUpload
			? $this->value->getError() !== UPLOAD_ERR_NO_FILE // ignore null object
			: (bool) $this->value;
	}


	/**
	 * Have been all files succesfully uploaded?
	 */
	public function isOk(): bool
	{
		return $this->value instanceof FileUpload
			? $this->value->isOk()
			: $this->value && array_reduce(
				$this->value,
				fn(bool $carry, FileUpload $fileUpload): bool => $carry && $fileUpload->isOk(),
				true,
			);
	}


	public function addRule(
		callable|string $validator,
		string|Stringable $errorMessage = null,
		mixed $arg = null,
	): static {
		if ($validator === Form::IMAGE) {
			$this->control->accept = implode(', ', FileUpload::IMAGE_MIME_TYPES);
		} elseif ($validator === Form::MIME_TYPE) {
			$this->control->accept = implode(', ', (array) $arg);
		} elseif ($validator === Form::MAX_FILE_SIZE) {
			if ($arg > Forms\Helpers::iniGetSize('upload_max_filesize')) {
				$ini = ini_get('upload_max_filesize');
				trigger_error("Value of MAX_FILE_SIZE ($arg) is greater than value of directive upload_max_filesize ($ini).", E_USER_WARNING);
			}
			$this->getRules()->removeRule($validator);
		}
		return parent::addRule($validator, $errorMessage, $arg);
	}
}
