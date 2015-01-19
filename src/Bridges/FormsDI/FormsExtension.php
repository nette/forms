<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Bridges\FormsDI;

use Nette;


/**
 * Forms extension for Nette DI.
 *
 * @author David Grudl
 * @author Miroslav Paulík
 */
class FormsExtension extends Nette\DI\CompilerExtension
{

	public $defaults = array(
		'messages' => array()
	);


	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$initialize = $class->methods['initialize'];

		$config = $this->compiler->getConfig();
		if ($oldSection = !isset($config[$this->name]) && isset($config['nette']['forms'])) {
			$config = Nette\DI\Config\Helpers::merge($config['nette']['forms'], $this->defaults);
			//trigger_error("Configuration section 'nette.forms' is deprecated, use section '$this->name' instead.", E_USER_DEPRECATED);
		} else {
			$config = $this->getConfig($this->defaults);
		}
		$this->validateConfig($this->defaults, $config, $oldSection ? 'nette.forms' : $this->name);

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
