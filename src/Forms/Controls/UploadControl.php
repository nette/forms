<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Forms;
use Nette\Http\FileUpload;


/**
 * Text box and browse button that allow users to select a file to upload to the server.
 */
class UploadControl extends BaseControl
{
	/** validation rule */
	public const VALID = ':uploadControlValid';


	/**
	 * @param  string|object  $label
	 */
	public function __construct($label = null, bool $multiple = false)
	{
		parent::__construct($label);
		$this->control->type = 'file';
		$this->control->multiple = $multiple;
		$this->setOption('type', 'file');
		$this->addRule([$this, 'isOk'], Forms\Validator::$messages[self::VALID]);

		$this->monitor(Forms\Form::class, function (Forms\Form $form): void {
			if (!$form->isMethod('post')) {
				throw new Nette\InvalidStateException('File upload requires method POST.');
			}
			$form->getElementPrototype()->enctype = 'multipart/form-data';
		});
	}


	/**
	 * Loads HTTP data.
	 */
	public function loadHttpData(): void
	{
		$this->value = $this->getHttpData(Nette\Forms\Form::DATA_FILE);
		if ($this->value === null) {
			$this->value = new FileUpload(null);
		}
	}


	/**
	 * Returns HTML name of control.
	 */
	public function getHtmlName(): string
	{
		return parent::getHtmlName() . ($this->control->multiple ? '[]' : '');
	}


	/**
	 * @return static
	 * @internal
	 */
	public function setValue($value)
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
			: $this->value && array_reduce($this->value, function (bool $carry, FileUpload $fileUpload): bool {
				return $carry && $fileUpload->isOk();
			}, true);
	}


	/**
	 * @return static
	 */
	public function addRule($validator, $errorMessage = null, $arg = null)
	{
		if ($validator === Forms\Form::IMAGE) {
			$this->control->accept = implode(FileUpload::IMAGE_MIME_TYPES, ', ');
		} elseif ($validator === Forms\Form::MIME_TYPE) {
			$this->control->accept = implode((array) $arg, ', ');
		}
		return parent::addRule($validator, $errorMessage, $arg);
	}
}
