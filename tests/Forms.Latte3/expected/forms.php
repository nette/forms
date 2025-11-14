<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* %a% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['id' => 'myForm', 'class' => 'ajax']) /* %a% */;
		echo "\n";
		foreach (['id', 'username', 'select', 'area', 'send'] as $name) /* %a% */ {
			echo '		';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item($name, $this->global)->getLabel()) /* %a% */;
			echo '
		';
			echo Nette\Bridges\FormsLatte\Runtime::item($name, $this->global)->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* %a% */;
			echo '
		';
			echo LR\%a%::escape%a%(Nette\Bridges\FormsLatte\Runtime::item($name, $this->global)->getError()) /* %a% */;
			echo '

		<br>

		';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item($form[$name], $this->global)->getLabel())?->addAttributes(['title' => 'hello'])?->startTag() /* %a% */;
			echo ' ';
			echo Nette\Bridges\FormsLatte\Runtime::item($form[$name], $this->global)->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* %a% */;
			echo ' ';
			echo $ʟ_label?->endTag() /* %a% */;
			echo '
		';
			echo LR\%a%::escape%a%(Nette\Bridges\FormsLatte\Runtime::item($form[$name], $this->global)->getError()) /* %a% */;
			echo "\n";

		}

		echo '
	';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getLabel()) /* %a% */;
		echo '

	<LABEL';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* %a% */;
		echo ' title=hello>Name</LABEL>
	<input value=val type class="hello"';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->addAttributes(['value' => null, 'type' => null, 'class' => null])->attributes() /* %a% */;
		echo '>

	<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getLabelPart())->attributes() /* %a% */;
		echo '></label>
	<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getLabelPart())->attributes() /* %a% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* %a% */;
		echo '</label>
	<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getControlPart())->attributes() /* %a% */;
		echo '>

	';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('my', $this->global)->getLabel()) /* %a% */;
		echo Nette\Bridges\FormsLatte\Runtime::item('my', $this->global)->getControl() /* %a% */;
		echo "\n";
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* %a% */;

		echo '


';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* %a% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* %a% */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* %a% */;

		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* %a% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* %a% */;
		echo "\n";
		foreach ($form['sex']->items as $key => $label) /* %a% */ {
			echo '	';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart($key))?->startTag() /* %a% */;
			echo ' ';
			echo Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getControlPart($key) /* %a% */;
			echo ' ';
			echo LR\%a%::escape%a%($label) /* %a% */;
			echo $ʟ_label?->endTag() /* %a% */;
			echo '
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart($key))->addAttributes(['title' => null])->attributes() /* %a% */;
			echo ' title=hello> <input';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getControlPart($key))->attributes() /* %a% */;
			echo '> </label>
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart($key))->addAttributes(['title' => null])->attributes() /* %a% */;
			echo ' title=hello>';
			echo $ʟ_elem->getHtml() /* %a% */;
			echo '</label>
';

		}

		echo '<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->attributes() /* %a% */;
		echo '></label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->attributes() /* %a% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* %a% */;
		echo '</label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* %a% */;
		echo ' title="hello">';
		echo $ʟ_elem->getHtml() /* %a% */;
		echo '</label>
<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['sex'], $this->global)->getControlPart("{$key}"))->attributes() /* %a% */;
		echo '>


';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart(''))?->startTag() /* %a% */;
		echo ' ';
		echo Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getControlPart('') /* %a% */;
		echo ' Label';
		echo $ʟ_label?->endTag() /* %a% */;
		echo '
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart(''))->addAttributes(['title' => null])->attributes() /* %a% */;
		echo ' title=hello> <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getControlPart(''))->attributes() /* %a% */;
		echo '> </label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* %a% */;
		echo ' title=hello> <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getControlPart())->attributes() /* %a% */;
		echo '> </label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart(''))->attributes() /* %a% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* %a% */;
		echo '</label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* %a% */;
		echo ' title=hello>';
		echo $ʟ_elem->getHtml() /* %a% */;
		echo '</label>


';
		foreach ($form['checklist']->items as $key => $label) /* %a% */ {
			echo '	';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart($key))?->startTag() /* %a% */;
			echo ' ';
			echo Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getControlPart($key) /* %a% */;
			echo ' ';
			echo LR\%a%::escape%a%($label) /* %a% */;
			echo $ʟ_label?->endTag() /* %a% */;
			echo '
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart($key))->attributes() /* %a% */;
			echo '> <input';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getControlPart($key))->addAttributes(['title' => null])->attributes() /* %a% */;
			echo ' title=hello> </label>
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart($key))->attributes() /* %a% */;
			echo '>';
			echo $ʟ_elem->getHtml() /* %a% */;
			echo '</label>
';

		}

		echo '<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart())->attributes() /* %a% */;
		echo '></label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart())->attributes() /* %a% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* %a% */;
		echo '</label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* %a% */;
		echo ' title="hello">';
		echo $ʟ_elem->getHtml() /* %a% */;
		echo '</label>


';
		if (1) /* %a% */ {
			$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* %a% */;
			Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
			echo '<form';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['id' => null, 'class' => null], false) /* %a% */;
			echo ' id="myForm" class="ajax">
	<input';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->attributes() /* %a% */;
			echo '>
';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* %a% */;
			echo '</form>
';
			array_pop($this->global->formsStack);
		}
		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* %a% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<form';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['class' => null], false) /* %a% */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\%a%::escape%a%(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* %a% */;
		echo '>
	<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* %a% */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\%a%::escape%a%(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* %a% */;
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* %a% */;
		echo '</form>
';
		array_pop($this->global->formsStack);
		echo '

<select';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('select', $this->global)->getControlPart())->attributes() /* %a% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* %a% */;
		echo '</select>


<textarea';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('area', $this->global)->getControlPart())->addAttributes(['title' => null])->attributes() /* %a% */;
		%A%
		echo $ʟ_elem->getHtml() /* %a% */;
		echo '</textarea>


<select';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('select', $this->global)->getControlPart())->attributes() /* %a% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* %a% */;
		echo '</select>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* %a% */;

		echo '


';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* %a% */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->attributes() /* %a% */;
		echo '>';
		echo $ʟ_elem->getHtml() /* %a% */;
		echo '</label>
<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->attributes() /* %a% */;
		echo '>
';
		array_pop($this->global->formsStack) /* %a% */;
%A%
