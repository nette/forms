<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* pos 1:7 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<form';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* pos 1:7 */;
		echo '>
	<button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->attributes() /* pos 2:10 */;
		echo '>
		description of button
	</button>

	<button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->attributes() /* pos 6:10 */;
		echo '></button>

	<button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->attributes() /* pos 8:10 */;
		echo '>';
		echo LR\HtmlHelpers::escapeText($ʟ_elem->value) /* pos 8:10 */;
		echo '</button>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* pos 1:7 */;
		echo '</form>
';
		array_pop($this->global->formsStack);
%A%
