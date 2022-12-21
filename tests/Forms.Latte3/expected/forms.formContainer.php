<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line %d% */;
		echo '
<table>
	<tr>
		<th>';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input1', $this->global)->getLabel()) /* line %d% */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input1', $this->global)->getControl() /* line %d% */;
		echo '</td>
	</tr>
';
		$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item('cont1', $this->global) /* line %d% */;
		echo '	<tr>
		<th>';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input2', $this->global)->getLabel()) /* line %d% */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input2', $this->global)->getControl() /* line %d% */;
		echo '</td>
	</tr>
	<tr>
		<th>';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input3', $this->global)->getLabel()) /* line %d% */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input3', $this->global)->getControl() /* line %d% */;
		echo '</td>
	</tr>
	<tr>
		<th>Checkboxes</th>
		<td>
';
		$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item('cont2', $this->global) /* line %d% */;
		echo '			<ol>
';
		foreach ($formContainer->controls as $name => $field) /* line %d% */ {
			echo '				<li>';
			echo Nette\Bridges\FormsLatte\Runtime::item($field, $this->global)->getControl() /* line %d% */;
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
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input7', $this->global)->getLabel()) /* line %d% */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input7', $this->global)->getControl() /* line %d% */;
		echo '</td>
	</tr>
';
		array_pop($this->global->formsStack);
		$formContainer = end($this->global->formsStack);

		$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item('items', $this->global) /* line %d% */;
		echo '	<tr>
		<th>Items</th>
		<td>
';
		$items = [1, 2, 3] /* line %d% */;
		foreach ($items as $item) /* line %d% */ {
			if (!isset($formContainer[$item])) /* line %d% */ continue;
			$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item($item, $this->global) /* line %d% */;
			echo '				';
			echo Nette\Bridges\FormsLatte\Runtime::item('input', $this->global)->getControl() /* line %d% */;
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
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input8', $this->global)->getLabel()) /* line %d% */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input8', $this->global)->getControl() /* line %d% */;
		echo '</td>
	</tr>
</table>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line %d% */;
%A%
