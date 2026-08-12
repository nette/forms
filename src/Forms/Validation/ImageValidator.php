<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Validation;

use Nette\Forms\Helpers;
use Nette\Http\FileUpload;
use function is_object;


/**
 * Validates that all uploaded files are images. By default accepts the formats
 * supported by the server (JPEG, PNG, GIF, WebP, AVIF...).
 */
final readonly class ImageValidator implements ValueValidator
{
	public const NotImage = 'forms.image';

	/** @var list<string> */
	public array $mimeTypes;


	/** @param  ?list<string>  $mimeTypes */
	public function __construct(?array $mimeTypes = null)
	{
		$this->mimeTypes = $mimeTypes ?? Helpers::getSupportedImages();
	}


	public function validate(mixed $value): ?Message
	{
		foreach (is_object($value) ? [$value] : (array) $value as $file) {
			if (!$file instanceof FileUpload) {
				continue;
			}

			$type = strtolower($file->getContentType() ?? '');
			if (!in_array($type, $this->mimeTypes, true)) {
				return new Message(
					self::NotImage,
					'The uploaded file must be an image in format %formats.',
					['formats' => $this->getFormats(), 'type' => $type, 'file' => $file->getUntrustedName()],
				);
			}
		}

		return null;
	}


	private function getFormats(): string
	{
		return implode(', ', array_map(fn(string $type): string => strtoupper(substr($type, 6)), $this->mimeTypes));
	}


	public function getClientRules(): array
	{
		return [new ClientRule(
			':image',
			arg: $this->mimeTypes,
			message: new Message(
				self::NotImage,
				'The uploaded file must be an image in format %formats.',
				['formats' => $this->getFormats()],
			),
			attributes: ['accept' => implode(', ', $this->mimeTypes)],
		)];
	}
}
