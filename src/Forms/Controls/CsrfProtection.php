<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Application\UI\Presenter;
use Stringable;


/**
 * CSRF protection field.
 */
class CsrfProtection extends HiddenField
{
	public const Protection = 'Nette\Forms\Controls\CsrfProtection::validateCsrf';

	/** @deprecated use CsrfProtection::Protection */
	public const PROTECTION = self::Protection;

	public ?Nette\Http\Session $session = null;


	public function __construct(string|Stringable|null $errorMessage = null)
	{
		parent::__construct();
		$this->setOmitted()
			->setRequired()
			->addRule(self::Protection, $errorMessage);

		$this->monitor(Presenter::class, function (Presenter $presenter): void {
			if (!$this->session) {
				$this->session = $presenter->getSession();
				$this->session->start();
			}
		});

		$this->monitor(Nette\Forms\Form::class, function (Nette\Forms\Form $form): void {
			if (!$this->session && !$form instanceof Nette\Application\UI\Form) {
				$this->session = new Nette\Http\Session($form->httpRequest, new Nette\Http\Response);
				$this->session->start();
			}
		});
	}


	/**
	 * @internal
	 */
	public function setValue($value): static
	{
		return $this;
	}


	public function loadHttpData(): void
	{
		$this->value = $this->getHttpData(Nette\Forms\Form::DataText);
	}


	public function getToken(): string
	{
		if (!$this->session) {
			throw new Nette\InvalidStateException('Session initialization error');
		}

		$session = $this->session->getSection(self::class);
		if (!$session->get('token')) {
			$session->set('token', Nette\Utils\Random::generate());
		}

		return $session->get('token') ^ $this->session->getId();
	}


	private function generateToken(?string $random = null): string
	{
		$random ??= Nette\Utils\Random::generate(10);
		return $random . base64_encode(sha1($this->getToken() . $random, binary: true));
	}


	public function getControl(): Nette\Utils\Html
	{
		return parent::getControl()->value($this->generateToken());
	}


	/** @internal */
	public static function validateCsrf(self $control): bool
	{
		$value = (string) $control->getValue();
		return $control->generateToken(substr($value, 0, 10)) === $value;
	}
}
