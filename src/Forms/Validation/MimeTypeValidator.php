<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Http\FileUpload;
use function is_array, is_object;


/**
 * Validates that all uploaded files match one of the allowed MIME types
 * (wildcards like 'image/*' are supported). The detected type of the offending
 * file is passed to the error message as the %type parameter.
 */
final readonly class MimeTypeValidator implements ValueValidator
{
	public const UnexpectedType = 'forms.mimeType';

	/** @var list<string> */
	public array $mimeTypes;


	/** @param  string|string[]  $mimeTypes */
	public function __construct(string|array $mimeTypes)
	{
		$this->mimeTypes = is_array($mimeTypes) ? array_values($mimeTypes) : explode(',', $mimeTypes);
	}


	public function validate(mixed $value): ?Message
	{
		foreach (is_object($value) ? [$value] : (array) $value as $file) {
			if (!$file instanceof FileUpload) {
				continue;
			}

			$type = strtolower($file->getContentType() ?? '');
			if (!$this->matches($type)) {
				return new Message(
					self::UnexpectedType,
					'The uploaded file is in an unsupported format %type.',
					['type' => $type, 'file' => $file->getUntrustedName()],
				);
			}
		}

		return null;
	}


	private function matches(string $type): bool
	{
		return in_array($type, $this->mimeTypes, true)
			|| in_array(preg_replace('#/.*#', '/*', $type), $this->mimeTypes, true);
	}


	public function getClientRules(): array
	{
		return [new ClientRule(
			':mimeType',
			arg: $this->mimeTypes,
			// the client knows only the browser-reported type, not the detected one
			message: new Message(self::UnexpectedType, 'The uploaded file is not in the expected format.'),
			attributes: ['accept' => implode(', ', $this->mimeTypes)],
		)];
	}
}
