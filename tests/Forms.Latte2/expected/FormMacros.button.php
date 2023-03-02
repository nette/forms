<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl["myForm"] /* line 1 */;
		echo '<form';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false);
		echo '>
	<button';
		$ʟ_input = $_input = end($this->global->formsStack)["send"];
		echo $ʟ_input->getControlPart()->attributes() /* line 2 */;
		echo '>
		description of button
	</button>

	<button';
		$ʟ_input = $_input = end($this->global->formsStack)["send"];
		echo $ʟ_input->getControlPart()->attributes() /* line 6 */;
		echo '></button>

	<button';
		$ʟ_input = $_input = end($this->global->formsStack)["send"];
		echo $ʟ_input->getControlPart()->attributes() /* line 8 */;
		echo '>';
		echo LR\Filters::escapeHtmlText($ʟ_input->getCaption()) /* line 8 */;
		echo '</button>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line 1 */;
		echo '</form>
';
%A%
