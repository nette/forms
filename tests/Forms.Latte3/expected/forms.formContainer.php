<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line %d% */;
		echo '
<table>
	<tr>
		<th>';
		if ($ʟ_label = end($this->global->formsStack)['input1']->getLabel()) echo $ʟ_label /* line %d% */;
		echo '</th>
		<td>';
		echo end($this->global->formsStack)['input1']->getControl() /* line %d% */;
		echo '</td>
	</tr>
';
		$this->global->formsStack[] = $formContainer = end($this->global->formsStack)['cont1'] /* line %d% */;
		echo '	<tr>
		<th>';
		if ($ʟ_label = end($this->global->formsStack)['input2']->getLabel()) echo $ʟ_label /* line %d% */;
		echo '</th>
		<td>';
		echo end($this->global->formsStack)['input2']->getControl() /* line %d% */;
		echo '</td>
	</tr>
	<tr>
		<th>';
		if ($ʟ_label = end($this->global->formsStack)['input3']->getLabel()) echo $ʟ_label /* line %d% */;
		echo '</th>
		<td>';
		echo end($this->global->formsStack)['input3']->getControl() /* line %d% */;
		echo '</td>
	</tr>
	<tr>
		<th>Checkboxes</th>
		<td>
';
		$this->global->formsStack[] = $formContainer = end($this->global->formsStack)['cont2'] /* line %d% */;
		echo '			<ol>
';
		foreach ($formContainer->controls as $name => $field) /* line %d% */ {
			echo '				<li>';
			$ʟ_input = is_object($ʟ_tmp = $field) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			echo $ʟ_input->getControl() /* line %d% */;
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
		if ($ʟ_label = end($this->global->formsStack)['input7']->getLabel()) echo $ʟ_label /* line %d% */;
		echo '</th>
		<td>';
		echo end($this->global->formsStack)['input7']->getControl() /* line %d% */;
		echo '</td>
	</tr>
';
		array_pop($this->global->formsStack);
		$formContainer = end($this->global->formsStack);

		$this->global->formsStack[] = $formContainer = end($this->global->formsStack)['items'] /* line %d% */;
		echo '	<tr>
		<th>Items</th>
		<td>
';
		$items = [1, 2, 3] /* line %d% */;
		foreach ($items as $item) /* line %d% */ {
			if (!isset($formContainer[$item])) /* line %d% */ continue;
			$this->global->formsStack[] = $formContainer = is_object($ʟ_tmp = $item) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp] /* line %d% */;
			echo '				';
			echo end($this->global->formsStack)['input']->getControl() /* line %d% */;
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
		if ($ʟ_label = end($this->global->formsStack)['input8']->getLabel()) echo $ʟ_label /* line %d% */;
		echo '</th>
		<td>';
		echo end($this->global->formsStack)['input8']->getControl() /* line %d% */;
		echo '</td>
	</tr>
</table>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line %d% */;
%A%
