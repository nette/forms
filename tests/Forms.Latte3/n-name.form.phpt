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
				$this->global->forms->begin($form = $this->global->uiControl['foo']) /* line 1 */;
				echo '<form';
				echo $this->global->forms->renderFormBegin([], false) /* line 1 */;
				echo '>';
				echo $this->global->forms->renderFormEnd(false) /* line 1 */;
				echo '</form>';
				$this->global->forms->end();
		%A%
		XX,
	$latte->compile('<form n:name="foo"></form>'),
);


Assert::match(
	<<<'XX'
		%A%
				$this->global->forms->begin($form = $this->global->uiControl['foo']) /* line 1 */;
				$ʟ_tag[0] = '';
				if (0) /* line 1 */ {
					echo '<';
					echo $ʟ_tmp = 'form' /* line 1 */;
					$ʟ_tag[0] = '</' . $ʟ_tmp . '>' . $ʟ_tag[0];
					echo $this->global->forms->renderFormBegin([], false) /* line 1 */;
					echo '>';
				}
				echo $this->global->forms->renderFormEnd(false) /* line 1 */;
				echo $ʟ_tag[0];
				$this->global->forms->end();
		%A%
		XX,
	$latte->compile('<form n:tag-if=0 n:name="foo"></form>'),
);
