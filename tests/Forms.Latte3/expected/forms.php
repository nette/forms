<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['id' => 'myForm', 'class' => 'ajax']) /* line %d% */;
		echo "\n";
		foreach (['id', 'username', 'select', 'area', 'send'] as $name) /* line %d% */ {
			echo '		';
			$ʟ_input = is_object($ʟ_tmp = $name) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			if ($ʟ_label = $ʟ_input->getLabel()) echo $ʟ_label /* line %d% */;
			echo '
		';
			$ʟ_input = is_object($ʟ_tmp = $name) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			echo $ʟ_input->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* line %d% */;
			echo '
		';
			$ʟ_input = is_object($ʟ_tmp = $name) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			echo LR\Filters::escapeHtmlText($ʟ_input->getError()) /* line %d% */;
			echo '

		<br>

		';
			$ʟ_input = is_object($ʟ_tmp = $form[$name]) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			if ($ʟ_label = $ʟ_input->getLabel()) echo $ʟ_label->addAttributes(['title' => 'hello'])->startTag() /* line %d% */;
			echo ' ';
			$ʟ_input = is_object($ʟ_tmp = $form[$name]) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			echo $ʟ_input->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* line %d% */;
			echo ' ';
			if ($ʟ_label) echo $ʟ_label->endTag() /* line %d% */;
			echo '
		';
			$ʟ_input = is_object($ʟ_tmp = $form[$name]) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
			echo LR\Filters::escapeHtmlText($ʟ_input->getError()) /* line %d% */;
			echo "\n";

		}

		echo '
	';
		$ʟ_input = is_object($ʟ_tmp = $form['username']) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
		if ($ʟ_label = $ʟ_input->getLabel()) echo $ʟ_label /* line %d% */;
		echo '

	<LABEL';
		$ʟ_input = end($this->global->formsStack)['username'];
		echo $ʟ_input->getLabelPart()->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello>Name</LABEL>
	<input value=val type class="hello"';
		$ʟ_input = end($this->global->formsStack)['username'];
		echo $ʟ_input->getControlPart()->addAttributes(['value' => null, 'type' => null, 'class' => null])->attributes() /* line %d% */;
		echo '>

	<label';
		$ʟ_input = is_object($ʟ_tmp = $form['username']) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
		echo $ʟ_input->getLabelPart()->attributes() /* line %d% */;
		echo '></label>
	<label';
		$ʟ_input = is_object($ʟ_tmp = $form['username']) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
		echo $ʟ_input->getLabelPart()->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line %d% */;
		echo '</label>
	<input';
		$ʟ_input = is_object($ʟ_tmp = $form['username']) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
		echo $ʟ_input->getControlPart()->attributes() /* line %d% */;
		echo '>

	';
		if ($ʟ_label = end($this->global->formsStack)['my']->getLabel()) echo $ʟ_label /* line %d% */;
		echo end($this->global->formsStack)['my']->getControl() /* line %d% */;
		echo "\n";
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line %d% */;

		echo '


';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line %d% */;

		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line %d% */;
		echo "\n";
		foreach ($form['sex']->items as $key => $label) /* line %d% */ {
			echo '	';
			if ($ʟ_label = end($this->global->formsStack)['sex']->getLabelPart($key)) echo $ʟ_label->startTag() /* line %d% */;
			echo ' ';
			echo end($this->global->formsStack)['sex']->getControlPart($key) /* line %d% */;
			echo ' ';
			echo LR\Filters::escapeHtmlText($label) /* line %d% */;
			if ($ʟ_label) echo $ʟ_label->endTag() /* line %d% */;
			echo '
	<label';
			$ʟ_input = end($this->global->formsStack)['sex'];
			echo $ʟ_input->getLabelPart($key)->addAttributes(['title' => null])->attributes() /* line %d% */;
			echo ' title=hello> <input';
			$ʟ_input = end($this->global->formsStack)['sex'];
			echo $ʟ_input->getControlPart($key)->attributes() /* line %d% */;
			echo '> </label>
';

		}

		echo '<label';
		$ʟ_input = end($this->global->formsStack)['sex'];
		echo $ʟ_input->getLabelPart()->attributes() /* line %d% */;
		echo '></label>
<label';
		$ʟ_input = end($this->global->formsStack)['sex'];
		echo $ʟ_input->getLabelPart()->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line %d% */;
		echo '</label>
<label';
		$ʟ_input = end($this->global->formsStack)['sex'];
		echo $ʟ_input->getLabelPart()->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title="hello">';
		echo $ʟ_input->getLabelPart()->getHtml() /* line %d% */;
		echo '</label>
<input';
		$ʟ_input = is_object($ʟ_tmp = $form['sex']) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];
		echo $ʟ_input->getControlPart("{$key}")->attributes() /* line %d% */;
		echo '>


';
		if ($ʟ_label = end($this->global->formsStack)['checkbox']->getLabelPart('')) echo $ʟ_label->startTag() /* line %d% */;
		echo ' ';
		echo end($this->global->formsStack)['checkbox']->getControlPart('') /* line %d% */;
		echo ' Label';
		if ($ʟ_label) echo $ʟ_label->endTag() /* line %d% */;
		echo '
<label';
		$ʟ_input = end($this->global->formsStack)['checkbox'];
		echo $ʟ_input->getLabelPart('')->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello> <input';
		$ʟ_input = end($this->global->formsStack)['checkbox'];
		echo $ʟ_input->getControlPart('')->attributes() /* line %d% */;
		echo '> </label>
<label';
		$ʟ_input = end($this->global->formsStack)['checkbox'];
		echo $ʟ_input->getLabelPart()->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello> <input';
		$ʟ_input = end($this->global->formsStack)['checkbox'];
		echo $ʟ_input->getControlPart()->attributes() /* line %d% */;
		echo '> </label>
<label';
		$ʟ_input = end($this->global->formsStack)['checkbox'];
		echo $ʟ_input->getLabelPart('')->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line %d% */;
		echo '</label>
<label';
		$ʟ_input = end($this->global->formsStack)['checkbox'];
		echo $ʟ_input->getLabelPart()->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line %d% */;
		echo '</label>


';
		foreach ($form['checklist']->items as $key => $label) /* line %d% */ {
			echo '	';
			if ($ʟ_label = end($this->global->formsStack)['checklist']->getLabelPart($key)) echo $ʟ_label->startTag() /* line %d% */;
			echo ' ';
			echo end($this->global->formsStack)['checklist']->getControlPart($key) /* line %d% */;
			echo ' ';
			echo LR\Filters::escapeHtmlText($label) /* line %d% */;
			if ($ʟ_label) echo $ʟ_label->endTag() /* line %d% */;
			echo '
	<label';
			$ʟ_input = end($this->global->formsStack)['checklist'];
			echo $ʟ_input->getLabelPart($key)->attributes() /* line %d% */;
			echo '> <input';
			$ʟ_input = end($this->global->formsStack)['checklist'];
			echo $ʟ_input->getControlPart($key)->addAttributes(['title' => null])->attributes() /* line %d% */;
			echo ' title=hello> </label>
';

		}

		echo '<label';
		$ʟ_input = end($this->global->formsStack)['checklist'];
		echo $ʟ_input->getLabelPart()->attributes() /* line %d% */;
		echo '></label>
<label';
		$ʟ_input = end($this->global->formsStack)['checklist'];
		echo $ʟ_input->getLabelPart()->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line %d% */;
		echo '</label>
<label';
		$ʟ_input = end($this->global->formsStack)['checklist'];
		echo $ʟ_input->getLabelPart()->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title="hello">';
		echo $ʟ_input->getLabelPart()->getHtml() /* line %d% */;
		echo '</label>


';
		if (1) /* line %d% */ {
			echo '<form';
			$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
			echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['id' => null, 'class' => null], false) /* line %d% */;
			echo ' id="myForm" class="ajax">
	<input';
			$ʟ_input = end($this->global->formsStack)['username'];
			echo $ʟ_input->getControlPart()->attributes() /* line %d% */;
			echo '>
';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line %d% */;
			echo '</form>
';
		}
		echo '

<form';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['class' => null], false) /* line %d% */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line %d% */;
		echo '>
	<input';
		$ʟ_input = end($this->global->formsStack)['username'];
		echo $ʟ_input->getControlPart()->addAttributes(['class' => null])->attributes() /* line %d% */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line %d% */;
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line %d% */;
		echo '</form>


<FORM';
		$form = $this->global->formsStack[] = is_object($ʟ_tmp = $this->global->uiControl['myForm']) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp] /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* line %d% */;
		echo '>
	<input';
		$ʟ_input = end($this->global->formsStack)['username'];
		echo $ʟ_input->getControlPart()->attributes() /* line %d% */;
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) /* line %d% */;
		echo '</FORM>


<select';
		$ʟ_input = end($this->global->formsStack)['select'];
		echo $ʟ_input->getControlPart()->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line %d% */;
		echo '</select>


<textarea';
		$ʟ_input = end($this->global->formsStack)['area'];
		echo $ʟ_input->getControlPart()->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title="';
		echo LR\Filters::escapeHtmlAttr(10) /* line %d% */;
		echo '">';
		echo $ʟ_input->getControl()->getHtml() /* line %d% */;
		echo '</textarea>


<select';
		$ʟ_input = end($this->global->formsStack)['select'];
		echo $ʟ_input->getControlPart()->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_input->getControl()->getHtml() /* line %d% */;
		echo '</select>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line %d% */;

		echo '


';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		echo '
<label';
		$ʟ_input = end($this->global->formsStack)['sex'];
		echo $ʟ_input->getLabelPart()->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_input->getLabelPart()->getHtml() /* line %d% */;
		echo '</label>
<input';
		$ʟ_input = end($this->global->formsStack)['username'];
		echo $ʟ_input->getControlPart()->attributes() /* line %d% */;
		echo '>
';
		array_pop($this->global->formsStack) /* line %d% */;
%A%
