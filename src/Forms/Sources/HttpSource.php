<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Sources;

use Nette;
use Nette\Forms\Controls\HiddenField;
use Nette\Forms\{Form, SubmissionSource};


/**
 * Source that detects submission by reading the form's own HTTP request; for use outside Nette Application. Experimental.
 */
final class HttpSource implements SubmissionSource
{
	/** Name of the hidden field pairing submitted data with the form. */
	public const TrackerId = '_form_';

	private Nette\Http\IRequest $request;

	/** which Sec-Fetch-Site values are accepted for submission; null disables the check */
	private ?Nette\Http\FetchSite $allowedOrigin = Nette\Http\FetchSite::SameOrigin;


	public function __construct(?Nette\Http\IRequest $request = null)
	{
		if ($request !== null) {
			$this->request = $request;
		}
	}


	/**
	 * Disables the same-origin (Sec-Fetch) CSRF check, allowing cross-origin form submissions.
	 */
	public function allowCrossOrigin(): void
	{
		$this->allowedOrigin = null;
	}


	public function receiveData(Form $form): ?array
	{
		$request = $this->getRequest();
		if (strcasecmp($form->getMethod(), $request->getMethod())) {
			return null;
		}

		if ($request->isMethod('post')) {
			if ($this->allowedOrigin !== null && !$request->isFrom($this->allowedOrigin)) {
				return null;
			}

			$data = Nette\Utils\Arrays::mergeTree($request->getPost(), $request->getFiles());

		} else {
			$data = $request->getQuery();
			if (!$data) {
				return null;
			}
		}

		$name = $form->getName();
		if ($name !== null && ($data[self::TrackerId] ?? null) !== $name) {
			return null;
		}

		return $data;
	}


	/**
	 * Installs the tracker field pairing the submitted data with a named form.
	 */
	public function prepare(Form $form): void
	{
		$name = $form->getName();
		if ($name !== null && !$form->getComponent(self::TrackerId, throw: false)) {
			$tracker = new HiddenField($name);
			$tracker->setOmitted();
			$form[self::TrackerId] = $tracker;
		}
	}


	private function getRequest(): Nette\Http\IRequest
	{
		return $this->request ??= (new Nette\Http\RequestFactory)->fromGlobals();
	}
}
