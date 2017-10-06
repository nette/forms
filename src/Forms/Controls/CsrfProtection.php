<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * CSRF protection field.
 */
class CsrfProtection extends HiddenField
{
	const PROTECTION = 'Nette\Forms\Controls\CsrfProtection::validateCsrf';

	/** @var Nette\Http\Session */
	public $session;


	/**
	 * @param string|object
	 */
	public function __construct($errorMessage)
	{
		parent::__construct();
		$this->setOmitted()
			->setRequired()
			->addRule(self::PROTECTION, $errorMessage);
		$this->monitor(Nette\Application\UI\Presenter::class);
	}


	protected function attached($parent)
	{
		parent::attached($parent);
		if (!$this->session && $parent instanceof Nette\Application\UI\Presenter) {
			$this->session = $parent->getSession();
		}
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
	 * @return void
	 */
	public function loadHttpData()
	{
		$this->value = $this->getHttpData(Nette\Forms\Form::DATA_TEXT);
	}


	/**
	 * @return string
	 */
	public function getToken()
	{
		$session = $this->getSession()->getSection(__CLASS__);
		if (!isset($session->token)) {
			$session->token = Nette\Utils\Random::generate();
		}
		return $session->token ^ $this->getSession()->getId();
	}


	/**
	 * @return string
	 */
	private function generateToken($random = null)
	{
		if ($random === null) {
			$random = Nette\Utils\Random::generate(10);
		}
		return $random . base64_encode(sha1($this->getToken() . $random, true));
	}


	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		return parent::getControl()->value($this->generateToken());
	}


	/**
	 * @return bool
	 * @internal
	 */
	public static function validateCsrf(self $control)
	{
		$value = (string) $control->getValue();
		return $control->generateToken(substr($value, 0, 10)) === $value;
	}


	/********************* backend ****************d*g**/


	/**
	 * @return Nette\Http\Session
	 */
	private function getSession()
	{
		if (!$this->session) {
			$this->session = new Nette\Http\Session($this->getForm()->httpRequest, new Nette\Http\Response);
		}
		return $this->session;
	}
}
