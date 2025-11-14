<?php

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

if (version_compare(Latte\Engine::VERSION, '3', '<')) {
	Tester\Environment::skip('Test for Latte 3');
}


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new FormsExtension);

Assert::match(
	<<<'XX'
		%A%
				$form = $this->global->formsStack[] = $this->global->uiControl['foo'] /* %a% */;
				Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
				echo '<form';
				echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* %a% */;
				echo '>';
				echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* %a% */;
				echo '</form>';
				array_pop($this->global->formsStack);
		%A%
		XX,
	$latte->compile('<form n:name="foo"></form>'),
);


Assert::match(
	<<<'XX'
		%A%
				$form = $this->global->formsStack[] = $this->global->uiControl['foo'] /* %a% */;
				Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
				%a%
				if (0) /* %a% */ {
					%A%
					echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* %a% */;
					echo '>';
				%A%
				echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* %a% */;
				%a%
				array_pop($this->global->formsStack);
		%A%
		XX,
	$latte->compile('<form n:tag-if=0 n:name="foo"></form>'),
);
