<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Http\FileUpload;
use function is_object;


/**
 * Validates that all uploaded files are within the size limit given in bytes.
 */
final readonly class FileSizeValidator implements ValueValidator
{
	public const TooBig = 'forms.fileSize';


	public function __construct(
		public int $limit,
	) {
	}


	public function validate(mixed $value): ?Message
	{
		foreach (is_object($value) ? [$value] : (array) $value as $file) {
			if (!$file instanceof FileUpload) {
				continue;
			}

			if ($file->getSize() > $this->limit || $file->getError() === UPLOAD_ERR_INI_SIZE) {
				return new Message(
					self::TooBig,
					'The size of the uploaded file can be up to %limit bytes.',
					['limit' => $this->limit, 'file' => $file->getUntrustedName()],
				);
			}
		}

		return null;
	}


	public function getClientRules(): array
	{
		return [new ClientRule(
			':fileSize',
			arg: $this->limit,
			message: new Message(self::TooBig, 'The size of the uploaded file can be up to %limit bytes.', ['limit' => $this->limit]),
		)];
	}
}
