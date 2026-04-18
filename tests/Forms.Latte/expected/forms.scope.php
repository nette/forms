<?php %A%
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos 3:1 */;
		echo '
	';
		echo ($ʟ_label = $this->global->forms->item('name')->getLabel()) /* pos 4:2 */;
		echo '
	';
		echo $this->global->forms->item('name')->getControl() /* pos 5:2 */;
		echo '
	';
		echo $this->global->forms->item('email')->getControl() /* pos 6:2 */;
		echo "\n";
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos 12:1 */;
		echo '
	';
		echo ($ʟ_label = $this->global->forms->item('name')->getLabel()) /* pos 13:2 */;
		echo '
	';
		echo $this->global->forms->item('name')->getControl() /* pos 14:2 */;
		echo '
	';
		echo $this->global->forms->item('email')->getControl() /* pos 15:2 */;
		echo "\n";
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos 21:1 */;
		echo '
	';
		echo $this->global->forms->item('name')->getControl() /* pos 22:2 */;
		echo "\n";
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos 28:1 */;
		echo $this->global->forms->renderFormBegin(['class' => 'outer']) /* pos 28:1 */;
		echo '
	';
		echo $this->global->forms->item('name')->getControl() /* pos 29:2 */;
		echo '

	';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos 31:2 */;
		echo '
		';
		echo $this->global->forms->item('email')->getControl() /* pos 32:3 */;
		echo '
	';
		$this->global->forms->end();

		echo "\n";
		echo $this->global->forms->renderFormEnd() /* pos 34:1 */;
		$this->global->forms->end();
%A%
