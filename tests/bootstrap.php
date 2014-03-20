<?php

// The Nette Tester command-line runner can be
// invoked through the command: ../vendor/bin/tester .

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}


Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');


function before(\Closure $function = NULL)
{
	static $val;
	if (!func_num_args()) {
		return ($val ? $val() : NULL);
	}
	$val = $function;
}


function test(\Closure $function)
{
	before();
	$function();
}
