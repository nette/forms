<?php
%A%
		echo Nette\Bridges\FormsLatte\Runtime2::renderFormBegin($form = $this->global->formsStack[] = $this->global->uiControl["myForm"], []) /* line 1 */;
		echo Nette\Bridges\FormsLatte\Runtime2::renderFormEnd(array_pop($this->global->formsStack));
		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl["myForm"] /* line 3 */;
		echo '<form';
		echo Nette\Bridges\FormsLatte\Runtime2::renderFormBegin(end($this->global->formsStack), [], false);
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime2::renderFormEnd(array_pop($this->global->formsStack), false) /* line 3 */;
		echo '</form>
';
%A%
