<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Forms\Controls;

use Nette;
use Stringable;
use function is_string;


/**
 * Submittable button control.
 *
 * @property-read bool $submittedBy
 */
class SubmitButton extends Button implements Nette\Forms\SubmitterControl
{
	/**
	 * Occurs when the button is clicked and form is successfully validated
	 * @var array<callable(self, array|object): void|callable(Nette\Forms\Form, array|object): void|callable(array|object): void>
	 */
	public array $onClick = [];

	/** @var array<callable(self): void>  Occurs when the button is clicked and form is not validated */
	public array $onInvalidClick = [];
	private ?array $validationScope = null;


	public function __construct(string|Stringable|null $caption = null)
	{
		parent::__construct($caption);
		$this->control->type = 'submit';
	}


	public function loadHttpData(): void
	{
		parent::loadHttpData();
		if ($this->isFilled()) {
			$this->getForm()->setSubmittedBy($this);
		}
	}


	/**
	 * Tells if the form was submitted by this button.
	 */
	public function isSubmittedBy(): bool
	{
		return $this->getForm()->isSubmitted() === $this;
	}


	/**
	 * Sets the validation scope. Clicking the button validates only the controls within the specified scope.
	 * @param  ?iterable<Nette\Forms\Control|Nette\Forms\Container|string>  $scope
	 */
	public function setValidationScope(?iterable $scope): static
	{
		if ($scope === null) {
			$this->validationScope = null;
			return $this;
		}

		$this->validationScope = [];
		foreach ($scope as $control) {
			if (is_string($control)) {
				$control = $this->getForm()->getComponent($control);
			}
			if (!$control instanceof Nette\Forms\Container && !$control instanceof Nette\Forms\Control) {
				throw new Nette\InvalidArgumentException('Validation scope accepts only Nette\Forms\Container or Nette\Forms\Control instances.');
			}

			$this->validationScope[] = $control;
		}
		return $this;
	}


	/**
	 * Gets the validation scope.
	 * @return ?array<Nette\Forms\Control|Nette\Forms\Container>
	 */
	public function getValidationScope(): ?array
	{
		return $this->validationScope;
	}


	/**
	 * Fires click event.
	 */
	public function click(): void
	{
		Nette\Utils\Arrays::invoke($this->onClick, $this);
	}


	public function getControl($caption = null): Nette\Utils\Html
	{
		$scope = [];
		foreach ((array) $this->validationScope as $control) {
			$scope[] = $control->lookupPath(Nette\Forms\Form::class);
		}

		return parent::getControl($caption)->addAttributes([
			'formnovalidate' => $this->validationScope !== null,
			'data-nette-validation-scope' => $scope ?: null,
		]);
	}
}
