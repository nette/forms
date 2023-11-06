<?php
%A%
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* line %d% */;
		echo '<form';
		echo $this->global->forms->renderFormBegin([], false) /* line %d% */;
		echo '>
	<button';
		echo ($ʟ_elem = $this->global->forms->item('send')->getControlPart())->attributes() /* line %d% */;
		echo '>
		description of button
	</button>

	<button';
		echo ($ʟ_elem = $this->global->forms->item('send')->getControlPart())->attributes() /* line %d% */;
		echo '></button>

	<button';
		echo ($ʟ_elem = $this->global->forms->item('send')->getControlPart())->attributes() /* line %d% */;
		echo '>';
		echo LR\Filters::escapeHtmlText($ʟ_elem->value) /* line %d% */;
		echo '</button>
';
		echo $this->global->forms->renderFormEnd(false) /* line %d% */;
		echo '</form>
';
		$this->global->forms->end();
%A%
