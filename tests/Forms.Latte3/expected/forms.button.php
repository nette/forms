<?php
%A%
		echo '<form';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* line %d% */;
		echo '>
	<button';
		echo ($ʟ_input = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global))->getControlPart()->attributes() /* line %d% */;
		echo '>
		description of button
	</button>

	<button';
		echo ($ʟ_input = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global))->getControlPart()->attributes() /* line %d% */;
		echo '></button>

	<button';
		echo ($ʟ_input = Nette\Bridges\FormsLatte\Runtime::item('send', $this->global))->getControlPart()->attributes() /* line %d% */;
		echo '>';
		echo LR\Filters::escapeHtmlText($ʟ_input->getCaption()) /* line %d% */;
		echo '</button>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line %d% */;
		echo '</form>
';
%A%
