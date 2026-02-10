<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* %a% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<form';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* %a% */;
		echo '>
	<button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->attributes() /* %a% */;
		echo '>
		description of button
	</button>

	<button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->attributes() /* %a% */;
		echo '></button>

	<button';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global)->getControlPart())->attributes() /* %a% */;
		echo '>';
		echo LR\%a%::escape%a%($ʟ_elem->value) /* %a% */;
		echo '</button>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* %a% */;
		echo '</form>
';
		array_pop($this->global->formsStack);
%A%
