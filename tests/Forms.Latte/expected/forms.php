<?php
%A%
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* pos %d%:1 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, ['id' => 'myForm', 'class' => 'ajax']) /* pos %d%:1 */;
		echo "\n";
		foreach (['id', 'username', 'select', 'area', 'send'] as $name) /* pos %d%:2 */ {
			echo '		';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item($name, $this->global)->getLabel()) /* pos %d%:3 */;
			echo '
		';
			echo Nette\Bridges\FormsLatte\Runtime::item($name, $this->global)->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* pos %d%:3 */;
			echo '
		';
			echo LR\HtmlHelpers::escapeText(Nette\Bridges\FormsLatte\Runtime::item($name, $this->global)->getError()) /* pos %d%:3 */;
			echo '

		<br>

		';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item($form[$name], $this->global)->getLabel())?->addAttributes(['title' => 'hello'])?->startTag() /* pos %d%:3 */;
			echo ' ';
			echo Nette\Bridges\FormsLatte\Runtime::item($form[$name], $this->global)->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* pos %d%:39 */;
			echo ' ';
			echo $ʟ_label?->endTag() /* pos %d%:87 */;
			echo '
		';
			echo LR\HtmlHelpers::escapeText(Nette\Bridges\FormsLatte\Runtime::item($form[$name], $this->global)->getError()) /* pos %d%:3 */;
			echo "\n";

		}

		echo '
	';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getLabel()) /* pos %d%:2 */;
		echo '

	<LABEL';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* pos %d%:9 */;
		echo ' title=hello>Name</LABEL>
	<input value=val type class="hello"';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->addAttributes(['value' => null, 'type' => null, 'class' => null])->attributes() /* pos %d%:38 */;
		echo '>

	<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getLabelPart())->attributes() /* pos %d%:9 */;
		echo '></label>
	<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getLabelPart())->attributes() /* pos %d%:9 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:9 */;
		echo '</label>
	<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['username'], $this->global)->getControlPart())->attributes() /* pos %d%:9 */;
		echo '>

	';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('my', $this->global)->getLabel()) /* pos %d%:2 */;
		echo Nette\Bridges\FormsLatte\Runtime::item('my', $this->global)->getControl() /* pos %d%:13 */;
		echo "\n";
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* pos %d%:1 */;

		echo '


';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* pos %d%:1 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* pos %d%:1 */;
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* pos %d%:1 */;

		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* pos %d%:1 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, []) /* pos %d%:1 */;
		echo "\n";
		foreach ($form['sex']->items as $key => $label) /* pos %d%:1 */ {
			echo '	';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart($key))?->startTag() /* pos %d%:2 */;
			echo ' ';
			echo Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getControlPart($key) /* pos %d%:19 */;
			echo ' ';
			echo LR\HtmlHelpers::escapeText($label) /* pos %d%:36 */;
			echo $ʟ_label?->endTag() /* pos %d%:44 */;
			echo '
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart($key))->addAttributes(['title' => null])->attributes() /* pos %d%:9 */;
			echo ' title=hello> <input';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getControlPart($key))->attributes() /* pos %d%:47 */;
			echo '> </label>
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart($key))->addAttributes(['title' => null])->attributes() /* pos %d%:9 */;
			echo ' title=hello>';
			echo $ʟ_elem->getHtml() /* pos %d%:9 */;
			echo '</label>
';

		}

		echo '<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->attributes() /* pos %d%:8 */;
		echo '></label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->attributes() /* pos %d%:8 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* pos %d%:8 */;
		echo ' title="hello">';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>
<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item($form['sex'], $this->global)->getControlPart("{$key}"))->attributes() /* pos %d%:8 */;
		echo '>


';
		echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart(''))?->startTag() /* pos %d%:1 */;
		echo ' ';
		echo Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getControlPart('') /* pos %d%:19 */;
		echo ' Label';
		echo $ʟ_label?->endTag() /* pos %d%:42 */;
		echo '
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart(''))->addAttributes(['title' => null])->attributes() /* pos %d%:8 */;
		echo ' title=hello> <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getControlPart(''))->attributes() /* pos %d%:47 */;
		echo '> </label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* pos %d%:8 */;
		echo ' title=hello> <input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getControlPart())->attributes() /* pos %d%:46 */;
		echo '> </label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart(''))->attributes() /* pos %d%:8 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checkbox', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* pos %d%:8 */;
		echo ' title=hello>';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>


';
		foreach ($form['checklist']->items as $key => $label) /* pos %d%:1 */ {
			echo '	';
			echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart($key))?->startTag() /* pos %d%:2 */;
			echo ' ';
			echo Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getControlPart($key) /* pos %d%:25 */;
			echo ' ';
			echo LR\HtmlHelpers::escapeText($label) /* pos %d%:48 */;
			echo $ʟ_label?->endTag() /* pos %d%:56 */;
			echo '
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart($key))->attributes() /* pos %d%:9 */;
			echo '> <input';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getControlPart($key))->addAttributes(['title' => null])->attributes() /* pos %d%:41 */;
			echo ' title=hello> </label>
	<label';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart($key))->attributes() /* pos %d%:9 */;
			echo '>';
			echo $ʟ_elem->getHtml() /* pos %d%:9 */;
			echo '</label>
';

		}

		echo '<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart())->attributes() /* pos %d%:8 */;
		echo '></label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart())->attributes() /* pos %d%:8 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('checklist', $this->global)->getLabelPart())->addAttributes(['title' => null])->attributes() /* pos %d%:8 */;
		echo ' title="hello">';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>


';
		if (1) /* pos %d%:48 */ {
			$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* pos %d%:7 */;
			Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
			echo '<form';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['id' => null, 'class' => null], false) /* pos %d%:7 */;
			echo ' id="myForm" class="ajax">
	<input';
			echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->attributes() /* pos %d%:9 */;
			echo '>
';
			echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* pos %d%:7 */;
			echo '</form>
';
			array_pop($this->global->formsStack);
		}
		echo '

';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* pos %d%:7 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '<form';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), ['class' => null], false) /* pos %d%:7 */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\HtmlHelpers::escapeAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* pos %d%:23 */;
		echo '>
	<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->addAttributes(['class' => null])->attributes() /* pos %d%:9 */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\HtmlHelpers::escapeAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* pos %d%:25 */;
		echo '>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(end($this->global->formsStack), false) /* pos %d%:7 */;
		echo '</form>
';
		array_pop($this->global->formsStack);
		echo '

<select';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('select', $this->global)->getControlPart())->attributes() /* pos %d%:9 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:9 */;
		echo '</select>


<textarea';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('area', $this->global)->getControlPart())->addAttributes(['title' => null])->attributes() /* pos %d%:11 */;
		echo LR\HtmlHelpers::formatAttribute(' title', 10) /* pos %d%:31 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:11 */;
		echo '</textarea>


<select';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('select', $this->global)->getControlPart())->attributes() /* pos %d%:9 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:9 */;
		echo '</select>
';
		echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack)) /* pos %d%:1 */;

		echo '


';
		$form = $this->global->formsStack[] = $this->global->uiControl['myForm'] /* pos %d%:1 */;
		Nette\Bridges\FormsLatte\Runtime::initializeForm($form);
		echo '
<label';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('sex', $this->global)->getLabelPart())->attributes() /* pos %d%:8 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>
<input';
		echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item('username', $this->global)->getControlPart())->attributes() /* pos %d%:8 */;
		echo '>
';
		array_pop($this->global->formsStack) /* pos %d%:1 */;
%A%
