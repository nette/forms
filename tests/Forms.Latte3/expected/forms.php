<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['id' => 'myForm', 'class' => 'ajax']) /* line %d% */;
		echo "\n";
		foreach (['id', 'username', 'select', 'area', 'send'] as $name) /* line %d% */ {
			echo '		';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item($name, $this->global)->getLabel()) /* line %d% */;
			echo '
		';
			echo Nette\Bridges\FormsLatte\Runtime::item($name, $this->global)->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* line %d% */;
			echo '
		';
			echo LR\Filters::escapeHtmlText(Nette\Bridges\FormsLatte\Runtime::item($name, $this->global)->getError()) /* line %d% */;
			echo '

		<br>

		';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item($form[$name], $this->global)->getLabel())?->addAttributes(['title' => 'hello'])?->startTag() /* line %d% */;
			echo ' ';
			echo Nette\Bridges\FormsLatte\Runtime::item($form[$name], $this->global)->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* line %d% */;
			echo ' ';
			echo $ʟ_label?->endTag() /* line %d% */;
			echo '
		';
			echo LR\Filters::escapeHtmlText(Nette\Bridges\FormsLatte\Runtime::item($form[$name], $this->global)->getError()) /* line %d% */;
			echo "\n";

		}

		echo '
	';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getLabel()) /* line %d% */;
		echo '

	<LABEL';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello>Name</LABEL>
	<input value=val type class="hello"';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->addAttributes(['value' => null, 'type' => null, 'class' => null])->attributes() /* line %d% */;
		echo '>

	<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getLabelPart())->attributes() /* line %d% */;
		echo '></label>
	<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getLabelPart())->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>
	<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getControlPart())->attributes() /* line %d% */;
		echo '>

	';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('my', $this->global)->getLabel()) /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::item('my', $this->global)->getControl() /* line %d% */;
		echo "\n";
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line %d% */;

		echo '


';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line %d% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line %d% */;

		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* line %d% */;
		echo "\n";
		foreach ($form['sex']->items as $key => $label) /* line %d% */ {
			echo '	';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart($key))?->startTag() /* line %d% */;
			echo ' ';
			echo Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getControlPart($key) /* line %d% */;
			echo ' ';
			echo LR\Filters::escapeHtmlText($label) /* line %d% */;
			echo $ʟ_label?->endTag() /* line %d% */;
			echo '
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart($key))->addAttributes(['title' => null])->attributes() /* line %d% */;
			echo ' title=hello> <input';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getControlPart($key))->attributes() /* line %d% */;
			echo '> </label>
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart($key))->addAttributes(['title' => null])->attributes() /* line %d% */;
			echo ' title=hello>';
			echo $ʟ_elem->getHtml() /* line %d% */;
			echo '</label>
';

		}

		echo '<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->attributes() /* line %d% */;
		echo '></label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title="hello">';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>
<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['sex'], $this->global)->getControlPart("{$key}"))->attributes() /* line %d% */;
		echo '>


';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart(''))?->startTag() /* line %d% */;
		echo ' ';
		echo Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getControlPart('') /* line %d% */;
		echo ' Label';
		echo $ʟ_label?->endTag() /* line %d% */;
		echo '
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart(''))->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello> <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getControlPart(''))->attributes() /* line %d% */;
		echo '> </label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello> <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getControlPart())->attributes() /* line %d% */;
		echo '> </label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart(''))->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>


';
		foreach ($form['checklist']->items as $key => $label) /* line %d% */ {
			echo '	';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart($key))?->startTag() /* line %d% */;
			echo ' ';
			echo Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getControlPart($key) /* line %d% */;
			echo ' ';
			echo LR\Filters::escapeHtmlText($label) /* line %d% */;
			echo $ʟ_label?->endTag() /* line %d% */;
			echo '
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart($key))->attributes() /* line %d% */;
			echo '> <input';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getControlPart($key))->addAttributes(['title' => null])->attributes() /* line %d% */;
			echo ' title=hello> </label>
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart($key))->attributes() /* line %d% */;
			echo '>';
			echo $ʟ_elem->getHtml() /* line %d% */;
			echo '</label>
';

		}

		echo '<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart())->attributes() /* line %d% */;
		echo '></label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart())->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title="hello">';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>


';
		if (1) /* line %d% */ {
			$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
			Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
			echo '<form';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['id' => null, 'class' => null], false) /* line %d% */;
			echo ' id="myForm" class="ajax">
	<input';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->attributes() /* line %d% */;
			echo '>
';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* line %d% */;
			echo '</form>
';
			array_pop($this->global->formsStack);
		}
		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<form';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['class' => null], false) /* line %d% */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line %d% */;
		echo '>
	<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* line %d% */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line %d% */;
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* line %d% */;
		echo '</form>
';
		array_pop($this->global->formsStack);
		echo '

';
		$form = $this->global->formsStack[] = is_object($ʟ_tmp = $this->global->uiControl['myForm']) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp] /* line %d% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<FORM';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), [], false) /* line %d% */;
		echo '>
	<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->attributes() /* line %d% */;
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* line %d% */;
		echo '</FORM>
';
		array_pop($this->global->formsStack);
		echo '

<select';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('select', $this->global)->getControlPart())->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</select>


<textarea';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('area', $this->global)->getControlPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title="';
		echo LR\Filters::escapeHtmlAttr(10) /* line %d% */;
		echo '">';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</textarea>


<select';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('select', $this->global)->getControlPart())->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</select>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* line %d% */;

		echo '


';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* line %d% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>
<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->attributes() /* line %d% */;
		echo '>
';
		array_pop($this->global->formsStack) /* line %d% */;
%A%
