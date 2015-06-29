<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\FormsDI;

use Nette;


/**
 * Forms extension for Nette DI.
 */
class FormsExtension extends Nette\DI\CompilerExtension
{
	public $defaults = array(
		'messages' => array(),
	);


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$initialize = $class->getMethod('initialize');
		$config = $this->validateConfig($this->defaults);

		foreach ((array) $config['messages'] as $name => $text) {
			if (defined('Nette\Forms\Form::' . $name)) {
				$initialize->addBody('Nette\Forms\Validator::$messages[Nette\Forms\Form::?] = ?;', array($name, $text));
			} elseif (defined($name)) {
				$initialize->addBody('Nette\Forms\Validator::$messages[' . $name . '] = ?;', array($text));
			} else {
				throw new Nette\InvalidArgumentException('Constant Nette\Forms\Form::' . $name . ' or constant ' . $name . ' does not exist.');
			}
		}
	}

}
