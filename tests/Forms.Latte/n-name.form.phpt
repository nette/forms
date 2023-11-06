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
				$this->global->forms->begin($form = $this->global->uiControl['foo']) /* pos 1:7 */;
				echo '<form';
				echo $this->global->forms->renderFormBegin([], false) /* pos 1:7 */;
				echo '>';
				echo $this->global->forms->renderFormEnd(false) /* pos 1:7 */;
				echo '</form>';
				$this->global->forms->end();
		%A%
		XX,
	$latte->compile('<form n:name="foo"></form>'),
);


Assert::match(
	<<<'XX'
		%A%
				$this->global->forms->begin($form = $this->global->uiControl['foo']) /* pos 1:18 */;
				$ʟ_tag = '';
				if (0) /* pos 1:7 */ {
					$ʟ_tag = '</form>' . $ʟ_tag;
					echo '<form';
					echo $this->global->forms->renderFormBegin([], false) /* pos 1:18 */;
					echo '>';
				}
				$ʟ_tags[0] = $ʟ_tag;
				echo $this->global->forms->renderFormEnd(false) /* pos 1:18 */;
				echo $ʟ_tags[0];
				$this->global->forms->end();
		%A%
		XX,
	$latte->compile('<form n:tag-if=0 n:name="foo"></form>'),
);
