<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Forms\Controls;

use Nette;


/**
 * Push button control with no default behavior.
 *
 * @property-read bool $submittedBy
 */
class Button extends SubmitButton implements Nette\Forms\ISubmitterControl
{
	/** @var callable[]  function (Button $sender); Occurs when the button is clicked and form is successfully validated */
	public $onClick;

	/** @var callable[]  function (Button $sender); Occurs when the button is clicked and form is not validated */
	public $onInvalidClick;

	/** @var array */
	private $validationScope;


	/**
	 * @param  string  caption
	 */
	public function __construct($caption = NULL)
	{
		parent::__construct($caption);
		$this->control->type = 'submit';
		$this->setOmitted(TRUE);
		$this->setOption('type', 'button');
	}


	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		parent::loadHttpData();
		if ($this->isFilled()) {
			$this->getForm()->setSubmittedBy($this);
		}
	}


	/**
	 * Is button pressed?
	 * @return bool
	 */
	public function isFilled()
	{
		$value = $this->getValue();
		return $this->control->type === 'submit' && $value !== NULL && $value !== [];
	}


	/**
	 * Tells if the form was submitted by this button.
	 * @return bool
	 */
	public function isSubmittedBy()
	{
		return $this->getForm()->isSubmitted() === $this;
	}


	/**
	 * Sets the validation scope. Clicking the button validates only the controls within the specified scope.
	 * @return self
	 */
	public function setValidationScope(/*array*/$scope = NULL)
	{
		if ($scope === NULL || $scope === TRUE) {
			$this->validationScope = NULL;
		} else {
			$this->validationScope = [];
			foreach ($scope ?: [] as $control) {
				if (!$control instanceof Nette\Forms\Container && !$control instanceof Nette\Forms\IControl) {
					throw new Nette\InvalidArgumentException('Validation scope accepts only Nette\Forms\Container or Nette\Forms\IControl instances.');
				}
				$this->validationScope[] = $control;
			}
		}
		return $this;
	}


	/**
	 * Gets the validation scope.
	 * @return array|NULL
	 */
	public function getValidationScope()
	{
		return $this->validationScope;
	}


	/**
	 * Fires click event.
	 * @return void
	 */
	public function click()
	{
		$this->onClick($this);
	}


	/**
	 * Bypasses label generation.
	 * @return void
	 */
	public function getLabel($caption = NULL)
	{
		return NULL;
	}


	/**
	 * Generates control's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getControl($caption = NULL)
	{
		$this->setOption('rendered', TRUE);
		$scope = [];
		foreach ((array) $this->validationScope as $control) {
			$scope[] = $control->lookupPath(Nette\Forms\Form::class);
		}
		$el = clone $this->control;
		return $el->addAttributes([
			'name' => $this->getHtmlName(),
			'disabled' => $this->isDisabled(),
			'value' => $this->translate($caption === NULL ? $this->caption : $caption),
			'formnovalidate' => $this->validationScope !== NULL,
			'data-nette-validation-scope' => $scope ?: NULL,
		]);
	}

}
