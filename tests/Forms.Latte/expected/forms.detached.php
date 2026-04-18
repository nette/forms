<?php %A%
		$this->global->forms->begin($form = $this->global->uiControl['myForm'], detached: true, global: $this->global) /* pos 5:1 */;
		echo $this->global->forms->renderFormBegin([]) /* pos 5:1 */;
		echo $this->global->forms->renderFormEnd() /* pos 12:1 */;
		echo '
	<div class="layout">
		';
		echo ($ʟ_label = $this->global->forms->get('name')->getLabel()) /* pos 7:3 */;
		echo '
		';
		echo $this->global->forms->get('name')->getControl() /* pos 8:3 */;
		echo '
		';
		echo $this->global->forms->get('email')->getControl() /* pos 9:3 */;
		echo '
		';
		echo $this->global->forms->get('submit')->getControl() /* pos 10:3 */;
		echo '
	</div>
';
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = $this->global->uiControl['myForm'], detached: true, global: $this->global) /* pos 17:1 */;
		echo $this->global->forms->renderFormBegin(['class' => 'shell']) /* pos 17:1 */;
		echo $this->global->forms->renderFormEnd() /* pos 19:1 */;
		echo '
	<div>';
		echo $this->global->forms->get('name')->getControl() /* pos 18:7 */;
		echo '</div>
';
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = $this->global->uiControl['myForm'], detached: true, global: $this->global) /* pos 24:1 */;
		echo $this->global->forms->renderFormBegin([]) /* pos 24:1 */;
		echo $this->global->forms->renderFormEnd() /* pos 33:1 */;
		echo '
	<div>
		';
		echo $this->global->forms->get('name')->getControl() /* pos 26:3 */;
		echo '
		<form action="/other">
			<input name="foo">
		</form>
		';
		echo $this->global->forms->get('submit')->getControl() /* pos 31:3 */;
		echo '
	</div>
';
		$this->global->forms->end();
%A%
