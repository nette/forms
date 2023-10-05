<?php

/** @phpVersion 8.0 */

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
				$form = $this->global->formsStack[] = $this->global->uiControl['foo'] /* line 1 */;
				echo '<form';
				echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* line 1 */;
				echo '>';
				echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* line 1 */;
				echo '</form>';
				array_pop($this->global->formsStack);
		%A%
		XX,
	$latte->compile('<form n:name="foo"></form>'),
);


Assert::match(
	<<<'XX'
		%A%
				$form = $this->global->formsStack[] = $this->global->uiControl['foo'] /* line 1 */;
				$ʟ_tag[0] = '';
				if (0) /* line 1 */ {
					echo '<';
					echo $ʟ_tmp = 'form' /* line 1 */;
					$ʟ_tag[0] = '</' . $ʟ_tmp . '>' . $ʟ_tag[0];
					echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* line 1 */;
					echo '>';
				}
				echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* line 1 */;
				echo $ʟ_tag[0];
				array_pop($this->global->formsStack);
		%A%
		XX,
	$latte->compile('<form n:tag-if=0 n:name="foo"></form>'),
);
