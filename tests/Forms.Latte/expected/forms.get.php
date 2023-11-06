<?php
%A%
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos 1:1 */;
		echo $this->global->forms->renderFormBegin([]) /* pos 1:1 */;
		echo $this->global->forms->renderFormEnd() /* pos 1:1 */;
		$this->global->forms->end();

		echo '

';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos 3:7 */;
		echo '<form';
		echo $this->global->forms->renderFormBegin([], false) /* pos 3:7 */;
		echo '>
';
		echo $this->global->forms->renderFormEnd(false) /* pos 3:7 */;
		echo '</form>
';
		$this->global->forms->end();
%A%