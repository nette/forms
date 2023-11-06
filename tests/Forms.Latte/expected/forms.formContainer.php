<?php
%A%
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos %d%:1 */;
		echo $this->global->forms->renderFormBegin([]) /* pos %d%:1 */;
		echo '
<table>
	<tr>
		<th>';
		echo ($ʟ_label = $this->global->forms->item('input1')->getLabel()) /* pos %d%:7 */;
		echo '</th>
		<td>';
		echo $this->global->forms->item('input1')->getControl() /* pos %d%:7 */;
		echo '</td>
	</tr>
';
		$this->global->forms->begin($formContainer = $this->global->forms->item('cont1')) /* pos %d%:2 */;
		echo '	<tr>
		<th>';
		echo ($ʟ_label = $this->global->forms->item('input2')->getLabel()) /* pos %d%:7 */;
		echo '</th>
		<td>';
		echo $this->global->forms->item('input2')->getControl() /* pos %d%:7 */;
		echo '</td>
	</tr>
	<tr>
		<th>';
		echo ($ʟ_label = $this->global->forms->item('input3')->getLabel()) /* pos %d%:7 */;
		echo '</th>
		<td>';
		echo $this->global->forms->item('input3')->getControl() /* pos %d%:7 */;
		echo '</td>
	</tr>
	<tr>
		<th>Checkboxes</th>
		<td>
';
		$this->global->forms->begin($formContainer = $this->global->forms->item('cont2')) /* pos %d%:8 */;
		echo '			<ol>
';
		foreach ($formContainer->controls as $name => $field) /* pos %d%:32 */ {
			echo '				<li>';
			echo $this->global->forms->item($field)->getControl() /* pos %d%:9 */;
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
		echo ($ʟ_label = $this->global->forms->item('input7')->getLabel()) /* pos %d%:7 */;
		echo '</th>
		<td>';
		echo $this->global->forms->item('input7')->getControl() /* pos %d%:7 */;
		echo '</td>
	</tr>
';
		$this->global->forms->end();
		$formContainer = $this->global->forms->current();

		$this->global->forms->begin($formContainer = $this->global->forms->item('items')) /* pos %d%:2 */;
		echo '	<tr>
		<th>Items</th>
		<td>
';
		$items = [1, 2, 3] /* pos %d%:3 */;
		foreach ($items as $item) /* pos %d%:3 */ {
			if (!isset($formContainer[$item])) /* pos %d%:4 */ continue;
			$this->global->forms->begin($formContainer = $this->global->forms->item($item)) /* pos %d%:4 */;
			echo '				';
			echo $this->global->forms->item('input')->getControl() /* pos %d%:5 */;
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
		echo ($ʟ_label = $this->global->forms->item('input8')->getLabel()) /* pos %d%:7 */;
		echo '</th>
		<td>';
		echo $this->global->forms->item('input8')->getControl() /* pos %d%:7 */;
		echo '</td>
	</tr>
</table>
';
		echo $this->global->forms->renderFormEnd() /* pos %d%:1 */;
		$this->global->forms->end();
%A%