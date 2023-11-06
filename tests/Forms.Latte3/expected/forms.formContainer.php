<?php
%A%
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* line %d% */;
		echo $this->global->forms->renderFormBegin([]) /* line %d% */;
		echo '
<table>
	<tr>
		<th>';
		echo ($ʟ_label = $this->global->forms->item('input1')->getLabel()) /* line %d% */;
		echo '</th>
		<td>';
		echo $this->global->forms->item('input1')->getControl() /* line %d% */;
		echo '</td>
	</tr>
';
		$this->global->forms->begin($formContainer = $this->global->forms->item('cont1')) /* line %d% */;
		echo '	<tr>
		<th>';
		echo ($ʟ_label = $this->global->forms->item('input2')->getLabel()) /* line %d% */;
		echo '</th>
		<td>';
		echo $this->global->forms->item('input2')->getControl() /* line %d% */;
		echo '</td>
	</tr>
	<tr>
		<th>';
		echo ($ʟ_label = $this->global->forms->item('input3')->getLabel()) /* line %d% */;
		echo '</th>
		<td>';
		echo $this->global->forms->item('input3')->getControl() /* line %d% */;
		echo '</td>
	</tr>
	<tr>
		<th>Checkboxes</th>
		<td>
';
		$this->global->forms->begin($formContainer = $this->global->forms->item('cont2')) /* line %d% */;
		echo '			<ol>
';
		foreach ($formContainer->controls as $name => $field) /* line %d% */ {
			echo '				<li>';
			echo $this->global->forms->item($field)->getControl() /* line %d% */;
			echo '</li>
';

		}

		echo '			</ol>
';
		$this->global->forms->end();
		$formContainer = $this->global->forms->current();

		echo '		</td>
	</tr>
	<tr>
		<th>';
		echo ($ʟ_label = $this->global->forms->item('input7')->getLabel()) /* line %d% */;
		echo '</th>
		<td>';
		echo $this->global->forms->item('input7')->getControl() /* line %d% */;
		echo '</td>
	</tr>
';
		$this->global->forms->end();
		$formContainer = $this->global->forms->current();

		$this->global->forms->begin($formContainer = $this->global->forms->item('items')) /* line %d% */;
		echo '	<tr>
		<th>Items</th>
		<td>
';
		$items = [1, 2, 3] /* line %d% */;
		foreach ($items as $item) /* line %d% */ {
			if (!isset($formContainer[$item])) /* line %d% */ continue;
			$this->global->forms->begin($formContainer = $this->global->forms->item($item)) /* line %d% */;
			echo '				';
			echo $this->global->forms->item('input')->getControl() /* line %d% */;
			echo "\n";
			$this->global->forms->end();
			$formContainer = $this->global->forms->current();


		}

		echo '		</td>
	</tr>
';
		$this->global->forms->end();
		$formContainer = $this->global->forms->current();

		echo '	<tr>
		<th>';
		echo ($ʟ_label = $this->global->forms->item('input8')->getLabel()) /* line %d% */;
		echo '</th>
		<td>';
		echo $this->global->forms->item('input8')->getControl() /* line %d% */;
		echo '</td>
	</tr>
</table>
';
		echo $this->global->forms->renderFormEnd() /* line %d% */;
		$this->global->forms->end();
%A%