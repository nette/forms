<?php
%A%
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* line %d% */;
		echo $this->global->forms->renderFormBegin(['id' => 'myForm', 'class' => 'ajax']) /* line %d% */;
		echo "\n";
		foreach (['id', 'username', 'select', 'area', 'send'] as $name) /* line %d% */ {
			echo '		';
			echo ($ʟ_label = $this->global->forms->item($name)->getLabel()) /* line %d% */;
			echo '
		';
			echo $this->global->forms->item($name)->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* line %d% */;
			echo '
		';
			echo LR\Filters::escapeHtmlText($this->global->forms->item($name)->getError()) /* line %d% */;
			echo '

		<br>

		';
			echo ($ʟ_label = $this->global->forms->item($form[$name])->getLabel())?->addAttributes(['title' => 'hello'])?->startTag() /* line %d% */;
			echo ' ';
			echo $this->global->forms->item($form[$name])->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* line %d% */;
			echo ' ';
			echo $ʟ_label?->endTag() /* line %d% */;
			echo '
		';
			echo LR\Filters::escapeHtmlText($this->global->forms->item($form[$name])->getError()) /* line %d% */;
			echo "\n";

		}

		echo '
	';
		echo ($ʟ_label = $this->global->forms->item($form['username'])->getLabel()) /* line %d% */;
		echo '

	<LABEL';
		echo ($ʟ_elem = $this->global->forms->item('username')->getLabelPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello>Name</LABEL>
	<input value=val type class="hello"';
		echo ($ʟ_elem = $this->global->forms->item('username')->getControlPart())->addAttributes(['value' => null, 'type' => null, 'class' => null])->attributes() /* line %d% */;
		echo '>

	<label';
		echo ($ʟ_elem = $this->global->forms->item($form['username'])->getLabelPart())->attributes() /* line %d% */;
		echo '></label>
	<label';
		echo ($ʟ_elem = $this->global->forms->item($form['username'])->getLabelPart())->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>
	<input';
		echo ($ʟ_elem = $this->global->forms->item($form['username'])->getControlPart())->attributes() /* line %d% */;
		echo '>

	';
		echo ($ʟ_label = $this->global->forms->item('my')->getLabel()) /* line %d% */;
		echo $this->global->forms->item('my')->getControl() /* line %d% */;
		echo "\n";
		echo $this->global->forms->renderFormEnd() /* line %d% */;
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* line %d% */;
		echo $this->global->forms->renderFormBegin([]) /* line %d% */;
		echo $this->global->forms->renderFormEnd() /* line %d% */;
		$this->global->forms->end();

		echo '

';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* line %d% */;
		echo $this->global->forms->renderFormBegin([]) /* line %d% */;
		echo "\n";
		foreach ($form['sex']->items as $key => $label) /* line %d% */ {
			echo '	';
			echo ($ʟ_label = $this->global->forms->item('sex')->getLabelPart($key))?->startTag() /* line %d% */;
			echo ' ';
			echo $this->global->forms->item('sex')->getControlPart($key) /* line %d% */;
			echo ' ';
			echo LR\Filters::escapeHtmlText($label) /* line %d% */;
			echo $ʟ_label?->endTag() /* line %d% */;
			echo '
	<label';
			echo ($ʟ_elem = $this->global->forms->item('sex')->getLabelPart($key))->addAttributes(['title' => null])->attributes() /* line %d% */;
			echo ' title=hello> <input';
			echo ($ʟ_elem = $this->global->forms->item('sex')->getControlPart($key))->attributes() /* line %d% */;
			echo '> </label>
	<label';
			echo ($ʟ_elem = $this->global->forms->item('sex')->getLabelPart($key))->addAttributes(['title' => null])->attributes() /* line %d% */;
			echo ' title=hello>';
			echo $ʟ_elem->getHtml() /* line %d% */;
			echo '</label>
';

		}

		echo '<label';
		echo ($ʟ_elem = $this->global->forms->item('sex')->getLabelPart())->attributes() /* line %d% */;
		echo '></label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('sex')->getLabelPart())->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('sex')->getLabelPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title="hello">';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>
<input';
		echo ($ʟ_elem = $this->global->forms->item($form['sex'])->getControlPart("{$key}"))->attributes() /* line %d% */;
		echo '>


';
		echo ($ʟ_label = $this->global->forms->item('checkbox')->getLabelPart(''))?->startTag() /* line %d% */;
		echo ' ';
		echo $this->global->forms->item('checkbox')->getControlPart('') /* line %d% */;
		echo ' Label';
		echo $ʟ_label?->endTag() /* line %d% */;
		echo '
<label';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getLabelPart(''))->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello> <input';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getControlPart(''))->attributes() /* line %d% */;
		echo '> </label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getLabelPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello> <input';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getControlPart())->attributes() /* line %d% */;
		echo '> </label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getLabelPart(''))->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getLabelPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title=hello>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>


';
		foreach ($form['checklist']->items as $key => $label) /* line %d% */ {
			echo '	';
			echo ($ʟ_label = $this->global->forms->item('checklist')->getLabelPart($key))?->startTag() /* line %d% */;
			echo ' ';
			echo $this->global->forms->item('checklist')->getControlPart($key) /* line %d% */;
			echo ' ';
			echo LR\Filters::escapeHtmlText($label) /* line %d% */;
			echo $ʟ_label?->endTag() /* line %d% */;
			echo '
	<label';
			echo ($ʟ_elem = $this->global->forms->item('checklist')->getLabelPart($key))->attributes() /* line %d% */;
			echo '> <input';
			echo ($ʟ_elem = $this->global->forms->item('checklist')->getControlPart($key))->addAttributes(['title' => null])->attributes() /* line %d% */;
			echo ' title=hello> </label>
	<label';
			echo ($ʟ_elem = $this->global->forms->item('checklist')->getLabelPart($key))->attributes() /* line %d% */;
			echo '>';
			echo $ʟ_elem->getHtml() /* line %d% */;
			echo '</label>
';

		}

		echo '<label';
		echo ($ʟ_elem = $this->global->forms->item('checklist')->getLabelPart())->attributes() /* line %d% */;
		echo '></label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('checklist')->getLabelPart())->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('checklist')->getLabelPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title="hello">';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</label>


';
		if (1) /* line %d% */ {
			$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* line %d% */;
			echo '<form';
			echo $this->global->forms->renderFormBegin(['id' => null, 'class' => null], false) /* line %d% */;
			echo ' id="myForm" class="ajax">
	<input';
			echo ($ʟ_elem = $this->global->forms->item('username')->getControlPart())->attributes() /* line %d% */;
			echo '>
';
			echo $this->global->forms->renderFormEnd(false) /* line %d% */;
			echo '</form>
';
			$this->global->forms->end();
		}
		echo '

';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* line %d% */;
		echo '<form';
		echo $this->global->forms->renderFormBegin(['class' => null], false) /* line %d% */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line %d% */;
		echo '>
	<input';
		echo ($ʟ_elem = $this->global->forms->item('username')->getControlPart())->addAttributes(['class' => null])->attributes() /* line %d% */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\Filters::escapeHtmlAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* line %d% */;
		echo '>
';
		echo $this->global->forms->renderFormEnd(false) /* line %d% */;
		echo '</form>
';
		$this->global->forms->end();
		echo '

';
		$this->global->forms->begin($form = (is_object($ʟ_tmp = $this->global->uiControl['myForm']) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp])) /* line %d% */;
		echo '<FORM';
		echo $this->global->forms->renderFormBegin([], false) /* line %d% */;
		echo '>
	<input';
		echo ($ʟ_elem = $this->global->forms->item('username')->getControlPart())->attributes() /* line %d% */;
		echo '>
';
		echo $this->global->forms->renderFormEnd(false) /* line %d% */;
		echo '</FORM>
';
		$this->global->forms->end();
		echo '

<select';
		echo ($ʟ_elem = $this->global->forms->item('select')->getControlPart())->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</select>


<textarea';
		echo ($ʟ_elem = $this->global->forms->item('area')->getControlPart())->addAttributes(['title' => null])->attributes() /* line %d% */;
		echo ' title="';
		echo LR\Filters::escapeHtmlAttr(10) /* line %d% */;
		echo '">';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</textarea>


<select';
		echo ($ʟ_elem = $this->global->forms->item('select')->getControlPart())->attributes() /* line %d% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* line %d% */;
		echo '</select>
';
		echo $this->global->forms->renderFormEnd() /* line %d% */;
		$this->global->forms->end();

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
