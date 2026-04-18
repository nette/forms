<?php %A%
		$this->global->forms->begin($form = (is_object($ʟ_tmp = 'myForm') ? $ʟ_tmp : ($this->global->forms->isNested() ? $this->global->forms->get($ʟ_tmp, Nette\Forms\Container::class) : $this->global->uiControl[$ʟ_tmp])), global: $this->global) /* pos 3:1 */;
		echo '
	';
		echo ($ʟ_label = $this->global->forms->get('name')->getLabel()) /* pos 4:2 */;
		echo '
	';
		echo $this->global->forms->get('name')->getControl() /* pos 5:2 */;
		echo "\n";
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = $this->global->uiControl['myForm'], global: $this->global) /* pos 11:1 */;
		echo '
	';
		echo ($ʟ_label = $this->global->forms->get('name')->getLabel()) /* pos 12:2 */;
		echo '
	';
		echo $this->global->forms->get('name')->getControl() /* pos 13:2 */;
		echo "\n";
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = $this->global->uiControl['myForm'], global: $this->global) /* pos 19:1 */;
		echo $this->global->forms->renderFormBegin(['class' => 'outer']) /* pos 19:1 */;
		echo '
	';
		echo $this->global->forms->get('name')->getControl() /* pos 20:2 */;
		echo '

	';
		$this->global->forms->begin($form = (is_object($ʟ_tmp = 'person') ? $ʟ_tmp : ($this->global->forms->isNested() ? $this->global->forms->get($ʟ_tmp, Nette\Forms\Container::class) : $this->global->uiControl[$ʟ_tmp])), global: $this->global) /* pos 22:2 */;
		echo '
		';
		echo $this->global->forms->get('city')->getControl() /* pos 23:3 */;
		echo '
	';
		$this->global->forms->end();

		echo "\n";
		echo $this->global->forms->renderFormEnd() /* pos 25:1 */;
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = $this->global->uiControl['myForm'], global: $this->global) /* pos 30:1 */;
		echo $this->global->forms->renderFormBegin(['class' => 'outer']) /* pos 30:1 */;
		echo '
	';
		echo $this->global->forms->get('name')->getControl() /* pos 31:2 */;
		echo '

';
		$this->global->forms->begin($formContainer = $this->global->forms->get('person', Nette\Forms\Container::class)) /* pos 33:2 */;
		echo '		';
		echo $this->global->forms->get('city')->getControl() /* pos 34:3 */;
		echo "\n";

		$this->global->forms->end();
		$formContainer = $this->global->forms->getScope();
		echo $this->global->forms->renderFormEnd() /* pos 36:1 */;
		$this->global->forms->end();

		echo "\n";
%A%
