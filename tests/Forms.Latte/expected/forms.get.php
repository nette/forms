<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* %a% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* %a% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* %a% */;

		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* %a% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<form';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* %a% */;
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* %a% */;
		echo '</form>
';
		array_pop($this->global->formsStack);
%A%
