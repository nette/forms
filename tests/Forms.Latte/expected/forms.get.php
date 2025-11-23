<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* pos 1:1 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* pos 1:1 */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* pos 1:1 */;

		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* pos 3:7 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<form';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* pos 3:7 */;
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* pos 3:7 */;
		echo '</form>
';
		array_pop($this->global->formsStack);
%A%
