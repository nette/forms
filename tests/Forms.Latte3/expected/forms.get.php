<?php
%A%
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* line %d% */;
		echo $this->global->forms->renderFormBegin([]) /* line %d% */;
		echo $this->global->forms->renderFormEnd() /* line %d% */;
		$this->global->forms->end();

		echo '

';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* line %d% */;
		echo '<form';
		echo $this->global->forms->renderFormBegin([], false) /* line %d% */;
		echo '>
';
		echo $this->global->forms->renderFormEnd(false) /* line %d% */;
		echo '</form>
';
		$this->global->forms->end();
%A%