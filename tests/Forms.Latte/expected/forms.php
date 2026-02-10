<?php
%A%
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos %d%:1 */;
		echo $this->global->forms->renderFormBegin(['id' => 'myForm', 'class' => 'ajax']) /* pos %d%:1 */;
		echo "\n";
		foreach (['id', 'username', 'select', 'area', 'send'] as $name) /* pos %d%:2 */ {
			echo '		';
			echo ($ʟ_label = $this->global->forms->item($name)->getLabel()) /* pos %d%:3 */;
			echo '
		';
			echo $this->global->forms->item($name)->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* pos %d%:3 */;
			echo '
		';
			echo LR\HtmlHelpers::escapeText($this->global->forms->item($name)->getError()) /* pos %d%:3 */;
			echo '

		<br>

		';
			echo ($ʟ_label = $this->global->forms->item($form[$name])->getLabel())?->addAttributes(['title' => 'hello'])?->startTag() /* pos %d%:3 */;
			echo ' ';
			echo $this->global->forms->item($form[$name])->getControl()->addAttributes(['title' => 'Hello', 'size' => 10]) /* pos %d%:39 */;
			echo ' ';
			echo $ʟ_label?->endTag() /* pos %d%:87 */;
			echo '
		';
			echo LR\HtmlHelpers::escapeText($this->global->forms->item($form[$name])->getError()) /* pos %d%:3 */;
			echo "\n";

		}

		echo '
	';
		echo ($ʟ_label = $this->global->forms->item($form['username'])->getLabel()) /* pos %d%:2 */;
		echo '

	<LABEL';
		echo ($ʟ_elem = $this->global->forms->item('username')->getLabelPart())->addAttributes(['title' => null])->attributes() /* pos %d%:9 */;
		echo ' title=hello>Name</LABEL>
	<input value=val type class="hello"';
		echo ($ʟ_elem = $this->global->forms->item('username')->getControlPart())->addAttributes(['value' => null, 'type' => null, 'class' => null])->attributes() /* pos %d%:38 */;
		echo '>

	<label';
		echo ($ʟ_elem = $this->global->forms->item($form['username'])->getLabelPart())->attributes() /* pos %d%:9 */;
		echo '></label>
	<label';
		echo ($ʟ_elem = $this->global->forms->item($form['username'])->getLabelPart())->attributes() /* pos %d%:9 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:9 */;
		echo '</label>
	<input';
		echo ($ʟ_elem = $this->global->forms->item($form['username'])->getControlPart())->attributes() /* pos %d%:9 */;
		echo '>

	';
		echo ($ʟ_label = $this->global->forms->item('my')->getLabel()) /* pos %d%:2 */;
		echo $this->global->forms->item('my')->getControl() /* pos %d%:13 */;
		echo "\n";
		echo $this->global->forms->renderFormEnd() /* pos %d%:1 */;
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos %d%:1 */;
		echo $this->global->forms->renderFormBegin([]) /* pos %d%:1 */;
		echo $this->global->forms->renderFormEnd() /* pos %d%:1 */;
		$this->global->forms->end();

		echo '

';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos %d%:1 */;
		echo $this->global->forms->renderFormBegin([]) /* pos %d%:1 */;
		echo "\n";
		foreach ($form['sex']->items as $key => $label) /* pos %d%:1 */ {
			echo '	';
			echo ($ʟ_label = $this->global->forms->item('sex')->getLabelPart($key))?->startTag() /* pos %d%:2 */;
			echo ' ';
			echo $this->global->forms->item('sex')->getControlPart($key) /* pos %d%:19 */;
			echo ' ';
			echo LR\HtmlHelpers::escapeText($label) /* pos %d%:36 */;
			echo $ʟ_label?->endTag() /* pos %d%:44 */;
			echo '
	<label';
			echo ($ʟ_elem = $this->global->forms->item('sex')->getLabelPart($key))->addAttributes(['title' => null])->attributes() /* pos %d%:9 */;
			echo ' title=hello> <input';
			echo ($ʟ_elem = $this->global->forms->item('sex')->getControlPart($key))->attributes() /* pos %d%:47 */;
			echo '> </label>
	<label';
			echo ($ʟ_elem = $this->global->forms->item('sex')->getLabelPart($key))->addAttributes(['title' => null])->attributes() /* pos %d%:9 */;
			echo ' title=hello>';
			echo $ʟ_elem->getHtml() /* pos %d%:9 */;
			echo '</label>
';

		}

		echo '<label';
		echo ($ʟ_elem = $this->global->forms->item('sex')->getLabelPart())->attributes() /* pos %d%:8 */;
		echo '></label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('sex')->getLabelPart())->attributes() /* pos %d%:8 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('sex')->getLabelPart())->addAttributes(['title' => null])->attributes() /* pos %d%:8 */;
		echo ' title="hello">';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>
<input';
		echo ($ʟ_elem = $this->global->forms->item($form['sex'])->getControlPart("{$key}"))->attributes() /* pos %d%:8 */;
		echo '>


';
		echo ($ʟ_label = $this->global->forms->item('checkbox')->getLabelPart(''))?->startTag() /* pos %d%:1 */;
		echo ' ';
		echo $this->global->forms->item('checkbox')->getControlPart('') /* pos %d%:19 */;
		echo ' Label';
		echo $ʟ_label?->endTag() /* pos %d%:42 */;
		echo '
<label';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getLabelPart(''))->addAttributes(['title' => null])->attributes() /* pos %d%:8 */;
		echo ' title=hello> <input';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getControlPart(''))->attributes() /* pos %d%:47 */;
		echo '> </label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getLabelPart())->addAttributes(['title' => null])->attributes() /* pos %d%:8 */;
		echo ' title=hello> <input';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getControlPart())->attributes() /* pos %d%:46 */;
		echo '> </label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getLabelPart(''))->attributes() /* pos %d%:8 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('checkbox')->getLabelPart())->addAttributes(['title' => null])->attributes() /* pos %d%:8 */;
		echo ' title=hello>';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>


';
		foreach ($form['checklist']->items as $key => $label) /* pos %d%:1 */ {
			echo '	';
			echo ($ʟ_label = $this->global->forms->item('checklist')->getLabelPart($key))?->startTag() /* pos %d%:2 */;
			echo ' ';
			echo $this->global->forms->item('checklist')->getControlPart($key) /* pos %d%:25 */;
			echo ' ';
			echo LR\HtmlHelpers::escapeText($label) /* pos %d%:48 */;
			echo $ʟ_label?->endTag() /* pos %d%:56 */;
			echo '
	<label';
			echo ($ʟ_elem = $this->global->forms->item('checklist')->getLabelPart($key))->attributes() /* pos %d%:9 */;
			echo '> <input';
			echo ($ʟ_elem = $this->global->forms->item('checklist')->getControlPart($key))->addAttributes(['title' => null])->attributes() /* pos %d%:41 */;
			echo ' title=hello> </label>
	<label';
			echo ($ʟ_elem = $this->global->forms->item('checklist')->getLabelPart($key))->attributes() /* pos %d%:9 */;
			echo '>';
			echo $ʟ_elem->getHtml() /* pos %d%:9 */;
			echo '</label>
';

		}

		echo '<label';
		echo ($ʟ_elem = $this->global->forms->item('checklist')->getLabelPart())->attributes() /* pos %d%:8 */;
		echo '></label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('checklist')->getLabelPart())->attributes() /* pos %d%:8 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>
<label';
		echo ($ʟ_elem = $this->global->forms->item('checklist')->getLabelPart())->addAttributes(['title' => null])->attributes() /* pos %d%:8 */;
		echo ' title="hello">';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>


';
		if (1) /* pos %d%:48 */ {
			$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos %d%:7 */;
			echo '<form';
			echo $this->global->forms->renderFormBegin(['id' => null, 'class' => null], false) /* pos %d%:7 */;
			echo ' id="myForm" class="ajax">
	<input';
			echo ($ʟ_elem = $this->global->forms->item('username')->getControlPart())->attributes() /* pos %d%:9 */;
			echo '>
';
			echo $this->global->forms->renderFormEnd(false) /* pos %d%:7 */;
			echo '</form>
';
			$this->global->forms->end();
		}
		echo '

';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos %d%:7 */;
		echo '<form';
		echo $this->global->forms->renderFormBegin(['class' => null], false) /* pos %d%:7 */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\HtmlHelpers::escapeAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* pos %d%:23 */;
		echo '>
	<input';
		echo ($ʟ_elem = $this->global->forms->item('username')->getControlPart())->addAttributes(['class' => null])->attributes() /* pos %d%:9 */;
		echo ($ʟ_tmp = array_filter(['nclass'])) ? ' class="' . LR\HtmlHelpers::escapeAttr(implode(" ", array_unique($ʟ_tmp))) . '"' : "" /* pos %d%:25 */;
		echo '>
';
		echo $this->global->forms->renderFormEnd(false) /* pos %d%:7 */;
		echo '</form>
';
		$this->global->forms->end();
		echo '

<select';
		echo ($ʟ_elem = $this->global->forms->item('select')->getControlPart())->attributes() /* pos %d%:9 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:9 */;
		echo '</select>


<textarea';
		echo ($ʟ_elem = $this->global->forms->item('area')->getControlPart())->addAttributes(['title' => null])->attributes() /* pos %d%:11 */;
		echo LR\HtmlHelpers::formatAttribute(' title', 10) /* pos %d%:31 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:11 */;
		echo '</textarea>


<select';
		echo ($ʟ_elem = $this->global->forms->item('select')->getControlPart())->attributes() /* pos %d%:9 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:9 */;
		echo '</select>
';
		echo $this->global->forms->renderFormEnd() /* pos %d%:1 */;
		$this->global->forms->end();

		echo '


';
		$this->global->forms->begin($form = $this->global->uiControl['myForm']) /* pos %d%:1 */;
		echo '
<label';
		echo ($ʟ_elem = $this->global->forms->item('sex')->getLabelPart())->attributes() /* pos %d%:8 */;
		echo '>';
		echo $ʟ_elem->getHtml() /* pos %d%:8 */;
		echo '</label>
<input';
		echo ($ʟ_elem = $this->global->forms->item('username')->getControlPart())->attributes() /* pos %d%:8 */;
		echo '>
';
		$this->global->forms->end();
%A%
