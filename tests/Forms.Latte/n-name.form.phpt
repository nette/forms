<?php

declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$latte = new Latte\Engine;
$latte->setLoader(new Latte\Loaders\StringLoader);
$latte->addExtension(new FormsExtension);

Assert::match(
	<<<'XX'
		%A%
				$form = $this->global->formsStack[] = $this->global->uiControl['foo'] /* pos 1:7 */;
				Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
				echo '<form';
				echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* pos 1:7 */;
				echo '>';
				echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* pos 1:7 */;
				echo '</form>';
				array_pop($this->global->formsStack);
		%A%
		XX,
	$latte->compile('<form n:name="foo"></form>'),
);


Assert::match(
	<<<'XX'
		%A%
				$form = $this->global->formsStack[] = $this->global->uiControl['foo'] /* pos 1:18 */;
				Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
				$ʟ_tag = '';
				if (0) /* pos 1:7 */ {
					$ʟ_tag = '</form>' . $ʟ_tag;
					echo '<form';
					echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* pos 1:18 */;
					echo '>';
				}
				$ʟ_tags[0] = $ʟ_tag;
				echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* pos 1:18 */;
				echo $ʟ_tags[0];
				array_pop($this->global->formsStack);
		%A%
		XX,
	$latte->compile('<form n:tag-if=0 n:name="foo"></form>'),
);
