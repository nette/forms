<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsDI;

use Nette;


/**
 * Forms extension for Nette DI.
 */
class FormsExtension extends Nette\DI\CompilerExtension
{
	public function __construct()
	{
		$this->config = new class {
			/** @var string[] */
			public $messages = [];
		};
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		if ($builder->findByType(Nette\Http\IRequest::class)) {
			$builder->addDefinition($this->prefix('factory'))
				->setFactory(Nette\Forms\FormFactory::class);
		}
	}


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$initialize = $this->initialization ?? $class->getMethod('initialize');

		foreach ($this->config->messages as $name => $text) {
			if (defined('Nette\Forms\Form::' . $name)) {
				$initialize->addBody('Nette\Forms\Validator::$messages[Nette\Forms\Form::?] = ?;', [$name, $text]);
			} elseif (defined($name)) {
				$initialize->addBody('Nette\Forms\Validator::$messages[' . $name . '] = ?;', [$text]);
			} else {
				throw new Nette\InvalidArgumentException('Constant Nette\Forms\Form::' . $name . ' or constant ' . $name . ' does not exist.');
			}
		}
	}
}
