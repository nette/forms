<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line %d% */;

		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<form';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* line %d% */;
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* line %d% */;
		echo '</form>
';
		array_pop($this->global->formsStack);
%A%
