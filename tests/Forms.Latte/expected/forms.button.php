<?php %A%
		$this->global->forms->begin($form = $this->global->uiControl['myForm'], global: $this->global) /* pos 1:7 */;
		echo '<form';
		echo $this->global->forms->renderFormBegin([], false) /* pos 1:7 */;
		echo '>
	<button';
		echo ($ʟ_elem = $this->global->forms->get('send')->getControlPart())->attributes() /* pos 2:10 */;
		echo '>
		description of button
	</button>

	<button';
		echo ($ʟ_elem = $this->global->forms->get('send')->getControlPart())->attributes() /* pos 6:10 */;
		echo '></button>

	<button';
		echo ($ʟ_elem = $this->global->forms->get('send')->getControlPart())->attributes() /* pos 8:10 */;
		echo '>';
		echo LR\HtmlHelpers::escapeText($ʟ_elem->getValue()) /* pos 8:10 */;
		echo '</button>
';
		echo $this->global->forms->renderFormEnd(false) /* pos 1:7 */;
		echo '</form>
';
		$this->global->forms->end();
%A%
