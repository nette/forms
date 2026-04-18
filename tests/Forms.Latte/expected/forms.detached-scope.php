<?php %A%
		$this->global->forms->begin($form = $this->global->uiControl['main'], detached: true) /* pos 4:1 */;
		echo $this->global->forms->renderFormBegin([]) /* pos 4:1 */;
		echo $this->global->forms->renderFormEnd() /* pos 9:1 */;
		echo '
	';
		echo $this->global->forms->get('name')->getControl() /* pos 5:2 */;
		echo '
	';
		$this->global->forms->begin($form = (is_object($ʟ_tmp = 'address') ? $ʟ_tmp : ($this->global->forms->isNested() ? $this->global->forms->get($ʟ_tmp, Nette\Forms\Container::class) : $this->global->uiControl[$ʟ_tmp]))) /* pos 6:2 */;
		echo '
		';
		echo $this->global->forms->get('street')->getControl() /* pos 7:3 */;
		echo '
	';
		$this->global->forms->end();

		echo "\n";
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = (is_object($ʟ_tmp = 'wrap') ? $ʟ_tmp : ($this->global->forms->isNested() ? $this->global->forms->get($ʟ_tmp, Nette\Forms\Container::class) : $this->global->uiControl[$ʟ_tmp]))) /* pos 16:1 */;
		echo '
	';
		echo $this->global->forms->get('title')->getControl() /* pos 17:2 */;
		echo '
	';
		$this->global->forms->begin($form = $this->global->uiControl['side'], detached: true) /* pos 18:2 */;
		echo $this->global->forms->renderFormBegin([]) /* pos 18:2 */;
		echo $this->global->forms->renderFormEnd() /* pos 20:2 */;
		echo '
		';
		echo $this->global->forms->get('note')->getControl() /* pos 19:3 */;
		echo '
	';
		$this->global->forms->end();

		echo "\n";
		$this->global->forms->end();

		echo "\n";
	}
%A%
