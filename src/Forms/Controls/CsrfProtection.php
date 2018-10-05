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

	/** @var Nette\Http\Session */
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


	/**
	 * Loads HTTP data.
	 */
	public function loadHttpData(): void
	{
		$this->value = $this->getHttpData(Nette\Forms\Form::DATA_TEXT);
	}


	public function getToken(): string
	{
		$session = $this->getSession()->getSection(__CLASS__);
		if (!isset($session->token)) {
			$session->token = Nette\Utils\Random::generate();
		}
		return $session->token ^ $this->getSession()->getId();
	}


	private function generateToken(string $random = null): string
	{
		if ($random === null) {
			$random = Nette\Utils\Random::generate(10);
		}
		return $random . base64_encode(sha1($this->getToken() . $random, true));
	}


	/**
	 * Generates control's HTML element.
	 */
	public function getControl(): Nette\Utils\Html
	{
		return parent::getControl()->value($this->generateToken());
	}


	/**
	 * @internal
	 */
	public static function validateCsrf(self $control): bool
	{
		$value = (string) $control->getValue();
		return $control->generateToken(substr($value, 0, 10)) === $value;
	}


	/********************* backend ****************d*g**/


	private function getSession(): Nette\Http\Session
	{
		if (!$this->session) {
			$this->session = new Nette\Http\Session($this->getForm()->httpRequest, new Nette\Http\Response);
		}
		return $this->session;
	}
}
