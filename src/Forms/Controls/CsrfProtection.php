<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Nette\Application\UI\Presenter;


/**
 * CSRF protection field.
 */
class CsrfProtection extends HiddenField
{
	public const PROTECTION = 'Nette\Forms\Controls\CsrfProtection::validateCsrf';

	/** @var Nette\Http\Session|null */
	public $session;


	/**
	 * @param string|object  $errorMessage
	 */
	public function __construct($errorMessage)
	{
		parent::__construct();
		$this->setOmitted()
			->setRequired()
			->addRule(self::PROTECTION, $errorMessage);

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
	 * @return static
	 * @internal
	 */
	public function setValue($value)
	{
		return $this;
	}


	public function loadHttpData(): void
	{
		$this->value = $this->getHttpData(Nette\Forms\Form::DATA_TEXT);
	}


	public function getToken(): string
	{
		if (!$this->session) {
			throw new Nette\InvalidStateException('Session initialization error');
		}
		$session = $this->session->getSection(self::class);
		if (!isset($session->token)) {
			$session->token = Nette\Utils\Random::generate();
		}
		return $session->token ^ $this->session->getId();
	}


	private function generateToken(string $random = null): string
	{
		if ($random === null) {
			$random = Nette\Utils\Random::generate(10);
		}
		return $random . base64_encode(sha1($this->getToken() . $random, true));
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
