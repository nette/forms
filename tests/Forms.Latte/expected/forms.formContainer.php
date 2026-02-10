<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* pos %d%:1 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* pos %d%:1 */;
		echo '
<table>
	<tr>
		<th>';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input1', $this->global)->getLabel()) /* pos %d%:7 */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input1', $this->global)->getControl() /* pos %d%:7 */;
		echo '</td>
	</tr>
';
		$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item('cont1', $this->global) /* pos %d%:2 */;
		echo '	<tr>
		<th>';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input2', $this->global)->getLabel()) /* pos %d%:7 */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input2', $this->global)->getControl() /* pos %d%:7 */;
		echo '</td>
	</tr>
	<tr>
		<th>';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input3', $this->global)->getLabel()) /* pos %d%:7 */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input3', $this->global)->getControl() /* pos %d%:7 */;
		echo '</td>
	</tr>
	<tr>
		<th>Checkboxes</th>
		<td>
';
		$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item('cont2', $this->global) /* pos %d%:8 */;
		echo '			<ol>
';
		foreach ($formContainer->controls as $name => $field) /* pos %d%:32 */ {
			echo '				<li>';
			echo Nette\Bridges\FormsLatte\Runtime::item($field, $this->global)->getControl() /* pos %d%:9 */;
			echo '</li>
';

		}

		echo '			</ol>
';
		array_pop($this->global->formsStack);
		$formContainer = end($this->global->formsStack);

		echo '		</td>
	</tr>
	<tr>
		<th>';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input7', $this->global)->getLabel()) /* pos %d%:7 */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input7', $this->global)->getControl() /* pos %d%:7 */;
		echo '</td>
	</tr>
';
		array_pop($this->global->formsStack);
		$formContainer = end($this->global->formsStack);

		$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item('items', $this->global) /* pos %d%:2 */;
		echo '	<tr>
		<th>Items</th>
		<td>
';
		$items = [1, 2, 3] /* pos %d%:3 */;
		foreach ($items as $item) /* pos %d%:3 */ {
			if (!isset($formContainer[$item])) /* pos %d%:4 */ continue;
			$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item($item, $this->global) /* pos %d%:4 */;
			echo '				';
			echo Nette\Bridges\FormsLatte\Runtime::item('input', $this->global)->getControl() /* pos %d%:5 */;
			echo "\n";
			array_pop($this->global->formsStack);
			$formContainer = end($this->global->formsStack);


		}

		echo '		</td>
	</tr>
';
		array_pop($this->global->formsStack);
		$formContainer = end($this->global->formsStack);

		echo '	<tr>
		<th>';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input8', $this->global)->getLabel()) /* pos %d%:7 */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input8', $this->global)->getControl() /* pos %d%:7 */;
		echo '</td>
	</tr>
</table>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* pos %d%:1 */;
%A%
