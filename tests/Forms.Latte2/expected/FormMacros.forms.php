<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl["myForm"];
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['id' => 'myForm', 'class'=>"ajax"]) /* line 1 */;
		echo "\n";
		$iterations = 0;
		foreach (['id', 'username', 'select', 'area', 'send'] as $name) /* line 2 */ {
			echo '		';
			$ʟ_input = is_object($ʟ_tmp = $name) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			if ($ʟ_label = $ʟ_input->getLabel()) echo $ʟ_label;
			echo '
		';
			$ʟ_input = $_input = is_object($ʟ_tmp = $name) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			echo $ʟ_input->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* line 4 */;
			echo '
		';
			echo LR\Filters::escapeHtmlText($ʟ_input->getError()) /* line 5 */;
			echo '
		';
			$ʟ_input = is_object($ʟ_tmp = $name) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			echo LR\Filters::escapeHtmlText($ʟ_input->getError()) /* line 6 */;
			echo '

		<br>

		';
			$ʟ_input = is_object($ʟ_tmp = $form[$name]) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			if ($ʟ_label = $ʟ_input->getLabel()) echo $ʟ_label->addAttributes(['title' => 'hello'])->startTag();
			echo ' ';
			$ʟ_input = $_input = is_object($ʟ_tmp = $form[$name]) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			echo $ʟ_input->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* line 10 */;
			echo ' ';
			if ($ʟ_label) echo $ʟ_label->endTag();
			echo '
		';
			$ʟ_input = is_object($ʟ_tmp = $form[$name]) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			echo LR\Filters::escapeHtmlText($ʟ_input->getError()) /* line 11 */;
			echo "\n";
			$iterations++;
		}
		echo '
	';
		$ʟ_input = is_object($ʟ_tmp = $form['username']) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
		if ($ʟ_label = $ʟ_input->getLabel()) echo $ʟ_label;
		echo '

	<LABEL title=hello';
		$ʟ_input = $_input = end($this->global->formsStack)["username"];
		echo $ʟ_input->getLabelPart()->addAttributes(['title' => null])->attributes() /* line 16 */;
		echo '>Name</LABEL>
	<input value=val type class="hello"';
		$ʟ_input = $_input = end($this->global->formsStack)["username"];
		echo $ʟ_input->getControlPart()->addAttributes(['value' => null, 'type' => null, 'class' => null])->attributes() /* line 17 */;
		echo '>

	<label';
		$ʟ_input = $_input = is_object($ʟ_tmp = $form['username']) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
		echo $ʟ_input->getLabelPart()->attributes() /* line 19 */;
		echo '></label>
	<label';
		$ʟ_input = $_input = is_object($ʟ_tmp = $form['username']) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
		echo $ʟ_input->getLabelPart()->attributes() /* line 20 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 20 */;
		echo '</label>
	<input';
		$ʟ_input = $_input = is_object($ʟ_tmp = $form['username']) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
		echo $ʟ_input->getControlPart()->attributes() /* line 21 */;
		echo '>

	';
		if ($ʟ_label = end($this->global->formsStack)["my"]->getLabel()) echo $ʟ_label;
		echo end($this->global->formsStack)["my"]->getControl() /* line 23 */;
		echo "\n";
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack));
		echo '


';
		$form = $this->global->formsStack[] = $this->global->uiControl["myForm"];
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 27 */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack));
		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl["myForm"];
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line 29 */;
		echo "\n";
		$iterations = 0;
		foreach ($form['sex']->items as $key => $label) /* line 31 */ {
			echo '	';
			if ($ʟ_label = end($this->global->formsStack)["sex"]->getLabelPart($key)) echo $ʟ_label->startTag();
			echo ' ';
			echo end($this->global->formsStack)["sex"]->getControlPart($key) /* line 32 */;
			echo ' ';
			echo LR\Filters::escapeHtmlText($label) /* line 32 */;
			if ($ʟ_label) echo $ʟ_label->endTag();
			echo '
	<label title=hello';
			$ʟ_input = $_input = end($this->global->formsStack)["sex"];
			echo $ʟ_input->getLabelPart($key)->addAttributes(['title' => null])->attributes() /* line 33 */;
			echo '> <input';
			$ʟ_input = $_input = end($this->global->formsStack)["sex"];
			echo $ʟ_input->getControlPart($key)->attributes() /* line 33 */;
			echo '> </label>
';
			$iterations++;
		}
		echo '<label';
		$ʟ_input = $_input = end($this->global->formsStack)["sex"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 35 */;
		echo '></label>
<label';
		$ʟ_input = $_input = end($this->global->formsStack)["sex"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 36 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 36 */;
		echo '</label>
<label title="hello"';
		$ʟ_input = $_input = end($this->global->formsStack)["sex"];
		echo $ʟ_input->getLabelPart()->addAttributes(['title' => null])->attributes() /* line 37 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 37 */;
		echo '</label>


';
		if ($ʟ_label = end($this->global->formsStack)["checkbox"]->getLabelPart("")) echo $ʟ_label->startTag();
		echo ' ';
		echo end($this->global->formsStack)["checkbox"]->getControlPart("") /* line 41 */;
		echo ' Label';
		if ($ʟ_label) echo $ʟ_label->endTag();
		echo '
<label title=hello';
		$ʟ_input = $_input = end($this->global->formsStack)["checkbox"];
		echo $ʟ_input->getLabelPart("")->addAttributes(['title' => null])->attributes() /* line 42 */;
		echo '> <input';
		$ʟ_input = $_input = end($this->global->formsStack)["checkbox"];
		echo $ʟ_input->getControlPart("")->attributes() /* line 42 */;
		echo '> </label>
<label title=hello';
		$ʟ_input = $_input = end($this->global->formsStack)["checkbox"];
		echo $ʟ_input->getLabelPart()->addAttributes(['title' => null])->attributes() /* line 43 */;
		echo '> <input';
		$ʟ_input = $_input = end($this->global->formsStack)["checkbox"];
		echo $ʟ_input->getControlPart()->attributes() /* line 43 */;
		echo '> </label>
<label';
		$ʟ_input = $_input = end($this->global->formsStack)["checkbox"];
		echo $ʟ_input->getLabelPart("")->attributes() /* line 44 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 44 */;
		echo '</label>
<label title=hello';
		$ʟ_input = $_input = end($this->global->formsStack)["checkbox"];
		echo $ʟ_input->getLabelPart()->addAttributes(['title' => null])->attributes() /* line 45 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 45 */;
		echo '</label>


';
		$iterations = 0;
		foreach ($form['checklist']->items as $key => $label) /* line 49 */ {
			echo '	';
			if ($ʟ_label = end($this->global->formsStack)["checklist"]->getLabelPart($key)) echo $ʟ_label->startTag();
			echo ' ';
			echo end($this->global->formsStack)["checklist"]->getControlPart($key) /* line 50 */;
			echo ' ';
			echo LR\Filters::escapeHtmlText($label) /* line 50 */;
			if ($ʟ_label) echo $ʟ_label->endTag();
			echo '
	<label';
			$ʟ_input = $_input = end($this->global->formsStack)["checklist"];
			echo $ʟ_input->getLabelPart($key)->attributes() /* line 51 */;
			echo '> <input title=hello';
			$ʟ_input = $_input = end($this->global->formsStack)["checklist"];
			echo $ʟ_input->getControlPart($key)->addAttributes(['title' => null])->attributes() /* line 51 */;
			echo '> </label>
';
			$iterations++;
		}
		echo '<label';
		$ʟ_input = $_input = end($this->global->formsStack)["checklist"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 53 */;
		echo '></label>
<label';
		$ʟ_input = $_input = end($this->global->formsStack)["checklist"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 54 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 54 */;
		echo '</label>
<label title="hello"';
		$ʟ_input = $_input = end($this->global->formsStack)["checklist"];
		echo $ʟ_input->getLabelPart()->addAttributes(['title' => null])->attributes() /* line 55 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 55 */;
		echo '</label>


';
		$form = $this->global->formsStack[] = $this->global->uiControl["myForm"] /* line 58 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		if (1) /* line 58 */ {
			echo '<form id="myForm" class="ajax"';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['id' => null, 'class' => null], false);
			echo '>
	<input';
			$ʟ_input = $_input = end($this->global->formsStack)["username"];
			echo $ʟ_input->getControlPart()->attributes() /* line 59 */;
			echo '>
';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line 58 */;
			echo '</form>
';
		}
		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl["myForm"] /* line 63 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<form';
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 63 */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['class' => null], false);
		echo '>
	<input';
		$ʟ_input = $_input = end($this->global->formsStack)["username"];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line 64 */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line 64 */;
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line 63 */;
		echo '</form>


';
		$form = $this->global->formsStack[] = is_object($ʟ_tmp = $this->global->uiControl['myForm']) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp] /* line 68 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<FORM';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false);
		echo '>
	<input';
		$ʟ_input = $_input = end($this->global->formsStack)["username"];
		echo $ʟ_input->getControlPart()->attributes() /* line 69 */;
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line 68 */;
		echo '</FORM>


<select';
		$ʟ_input = $_input = end($this->global->formsStack)["select"];
		echo $ʟ_input->getControlPart()->attributes() /* line 73 */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line 73 */;
		echo '</select>


<textarea title="';
		echo LR\Filters::escapeHtmlAttr(10) /* line 76 */;
		echo '"';
		$ʟ_input = $_input = end($this->global->formsStack)["area"];
		echo $ʟ_input->getControlPart()->addAttributes(['title' => null])->attributes() /* line 76 */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line 76 */;
		echo '</textarea>


<select';
		$ʟ_input = $_input = end($this->global->formsStack)["select"];
		echo $ʟ_input->getControlPart()->attributes() /* line 79 */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line 79 */;
		echo '</select>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack));
		echo '


';
		$form = $this->global->formsStack[] = $this->global->uiControl["myForm"] /* line 83 */;
		echo '<label';
		$ʟ_input = $_input = end($this->global->formsStack)["sex"];
		echo $ʟ_input->getLabelPart()->attributes() /* line 84 */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line 84 */;
		echo '</label>
<input';
		$ʟ_input = $_input = end($this->global->formsStack)["username"];
		echo $ʟ_input->getControlPart()->attributes() /* line 85 */;
		echo '>
';
		array_pop($this->global->formsStack);
%A%
