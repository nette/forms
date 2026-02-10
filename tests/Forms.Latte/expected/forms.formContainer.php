<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* %a% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* %a% */;
		echo '
<table>
	<tr>
		<th>';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input1', $this->global)->getLabel()) /* %a% */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input1', $this->global)->getControl() /* %a% */;
		echo '</td>
	</tr>
';
		$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item('cont1', $this->global) /* %a% */;
		echo '	<tr>
		<th>';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input2', $this->global)->getLabel()) /* %a% */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input2', $this->global)->getControl() /* %a% */;
		echo '</td>
	</tr>
	<tr>
		<th>';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input3', $this->global)->getLabel()) /* %a% */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input3', $this->global)->getControl() /* %a% */;
		echo '</td>
	</tr>
	<tr>
		<th>Checkboxes</th>
		<td>
';
		$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item('cont2', $this->global) /* %a% */;
		echo '			<ol>
';
		foreach ($formContainer->controls as $name => $field) /* %a% */ {
			echo '				<li>';
			echo Nette\Bridges\FormsLatte\Runtime::item($field, $this->global)->getControl() /* %a% */;
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
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input7', $this->global)->getLabel()) /* %a% */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input7', $this->global)->getControl() /* %a% */;
		echo '</td>
	</tr>
';
		array_pop($this->global->formsStack);
		$formContainer = end($this->global->formsStack);

		$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item('items', $this->global) /* %a% */;
		echo '	<tr>
		<th>Items</th>
		<td>
';
		$items = [1, 2, 3] /* %a% */;
		foreach ($items as $item) /* %a% */ {
			if (!isset($formContainer[$item])) /* %a% */ continue;
			$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item($item, $this->global) /* %a% */;
			echo '				';
			echo Nette\Bridges\FormsLatte\Runtime::item('input', $this->global)->getControl() /* %a% */;
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
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('input8', $this->global)->getLabel()) /* %a% */;
		echo '</th>
		<td>';
		echo Nette\Bridges\FormsLatte\Runtime::item('input8', $this->global)->getControl() /* %a% */;
		echo '</td>
	</tr>
</table>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* %a% */;
%A%
