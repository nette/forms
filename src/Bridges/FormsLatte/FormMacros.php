<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte;

use Latte;
use Latte\CompileException;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;


/**
 * Latte v2 macros for Nette\Forms.
 *
 * - {form name} ... {/form}
 * - {input name}
 * - {label name /} or {label name}... {/label}
 * - {inputError name}
 * - {formContainer name} ... {/formContainer}
 * - {formContext name} ... {/formContext}
 */
final class FormMacros extends MacroSet
{
	public static function install(Latte\Compiler $compiler): void
	{
		$me = new static($compiler);
		$me->addMacro('form', [$me, 'macroForm'], 'echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack));');
		$me->addMacro('formContext', [$me, 'macroFormContext'], 'array_pop($this->global->formsStack);');
		$me->addMacro('formContainer', [$me, 'macroFormContainer'], 'array_pop($this->global->formsStack); $formContainer = end($this->global->formsStack)');
		$me->addMacro('label', [$me, 'macroLabel'], [$me, 'macroLabelEnd'], null, self::AUTO_EMPTY);
		$me->addMacro('input', [$me, 'macroInput']);
		$me->addMacro('name', [$me, 'macroName'], [$me, 'macroNameEnd'], [$me, 'macroNameAttr']);
		$me->addMacro('inputError', [$me, 'macroInputError']);
		$me->addMacro('formPrint', [$me, 'macroFormPrint']);
		$me->addMacro('formClassPrint', [$me, 'macroFormPrint']);
	}


	/********************* macros ****************d*g**/


	/**
	 * {form ...}
	 */
	public function macroForm(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers) {
			throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
		}

		if ($node->prefix) {
			throw new CompileException('Did you mean <form n:name=...> ?');
		}

		$name = $node->tokenizer->fetchWord();
		if ($name == null) { // null or false
			throw new CompileException('Missing form name in ' . $node->getNotation());
		}

		$node->replaced = true;
		$node->tokenizer->reset();
		return $writer->write(
			'echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form = $this->global->formsStack[] = '
			. ($name[0] === '$'
				? 'is_object($ʟ_tmp = %node.word) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]'
				: '$this->global->uiControl[%node.word]')
			. ', %node.array)'
			. " /* line $node->startLine */;"
		);
	}


	/**
	 * {formContext ...}
	 */
	public function macroFormContext(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers) {
			throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
		}

		if ($node->prefix) {
			throw new CompileException('Did you mean <form n:name=...> ?');
		}

		$name = $node->tokenizer->fetchWord();
		if ($name == null) { // null or false
			throw new CompileException('Missing form name in ' . $node->getNotation());
		}

		$node->tokenizer->reset();
		return $writer->write(
			'$form = $this->global->formsStack[] = '
			. ($name[0] === '$'
				? 'is_object($ʟ_tmp = %node.word) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]'
				: '$this->global->uiControl[%node.word]')
			. " /* line $node->startLine */;"
		);
	}


	/**
	 * {formContainer ...}
	 */
	public function macroFormContainer(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers) {
			throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
		}

		$name = $node->tokenizer->fetchWord();
		if ($name == null) { // null or false
			throw new CompileException('Missing name in ' . $node->getNotation());
		}

		$node->tokenizer->reset();
		return $writer->write(
			'$this->global->formsStack[] = $formContainer = '
			. ($name[0] === '$'
				? 'is_object($ʟ_tmp = %node.word) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp]'
				: 'end($this->global->formsStack)[%node.word]')
			. " /* line $node->startLine */;"
		);
	}


	/**
	 * {label ...}
	 */
	public function macroLabel(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers) {
			throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
		}

		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException('Missing name in ' . $node->getNotation());
		}

		$node->replaced = true;
		$name = array_shift($words);
		return $writer->write(
			($name[0] === '$'
				? '$ʟ_input = is_object($ʟ_tmp = %0.word) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp]; if ($ʟ_label = $ʟ_input'
				: 'if ($ʟ_label = end($this->global->formsStack)[%0.word]'
			)
			. '->%1.raw) echo $ʟ_label'
			. ($node->tokenizer->isNext() ? '->addAttributes(%node.array)' : ''),
			$name,
			$words ? ('getLabelPart(' . implode(', ', array_map([$writer, 'formatWord'], $words)) . ')') : 'getLabel()'
		);
	}


	/**
	 * {/label}
	 */
	public function macroLabelEnd(MacroNode $node, PhpWriter $writer)
	{
		if ($node->content != null) {
			$node->openingCode = rtrim($node->openingCode, '?> ') . '->startTag() ?>';
			return $writer->write('if ($ʟ_label) echo $ʟ_label->endTag()');
		}
	}


	/**
	 * {input ...}
	 */
	public function macroInput(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers) {
			throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
		}

		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException('Missing name in ' . $node->getNotation());
		}

		$node->replaced = true;
		$name = array_shift($words);
		return $writer->write(
			($name[0] === '$'
				? '$ʟ_input = $_input = is_object($ʟ_tmp = %word) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp]; echo $ʟ_input'
				: 'echo end($this->global->formsStack)[%word]')
			. '->%raw'
			. ($node->tokenizer->isNext() ? '->addAttributes(%node.array)' : '')
			. " /* line $node->startLine */;",
			$name,
			$words ? 'getControlPart(' . implode(', ', array_map([$writer, 'formatWord'], $words)) . ')' : 'getControl()'
		);
	}


	/**
	 * <form n:name>, <input n:name>, <select n:name>, <textarea n:name>, <label n:name> and <button n:name>
	 */
	public function macroNameAttr(MacroNode $node, PhpWriter $writer)
	{
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException('Missing name in ' . $node->getNotation());
		}

		$name = array_shift($words);
		$tagName = strtolower($node->htmlNode->name);
		$node->empty = $tagName === 'input';

		$definedHtmlAttributes = array_keys($node->htmlNode->attrs);
		if (isset($node->htmlNode->macroAttrs['class'])) {
			$definedHtmlAttributes[] = 'class';
		}

		if ($tagName === 'form') {
			$node->openingCode = $writer->write(
				'<?php $form = $this->global->formsStack[] = '
				. ($name[0] === '$'
					? 'is_object($ʟ_tmp = %0.word) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]'
					: '$this->global->uiControl[%0.word]')
				. " /* line $node->startLine */; ?>",
				$name
			);
			return $writer->write(
				'echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), %0.var, false)',
				array_fill_keys($definedHtmlAttributes, null)
			);
		} else {
			$method = $tagName === 'label' ? 'getLabel' : 'getControl';
			return $writer->write(
				'$ʟ_input = $_input = '
				. ($name[0] === '$'
					? 'is_object($ʟ_tmp = %0.word) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp]'
					: 'end($this->global->formsStack)[%0.word]')
				. '; echo $ʟ_input->%1.raw'
				. ($definedHtmlAttributes ? '->addAttributes(%2.var)' : '') . '->attributes()'
				. " /* line $node->startLine */;",
				$name,
				$method . 'Part(' . implode(', ', array_map([$writer, 'formatWord'], $words)) . ')',
				array_fill_keys($definedHtmlAttributes, null)
			);
		}
	}


	public function macroName(MacroNode $node, PhpWriter $writer)
	{
		if (!$node->prefix) {
			throw new CompileException("Unknown tag {{$node->name}}, use n:{$node->name} attribute.");
		} elseif ($node->prefix !== MacroNode::PREFIX_NONE) {
			throw new CompileException("Unknown attribute n:{$node->prefix}-{$node->name}, use n:{$node->name} attribute.");
		}
	}


	public function macroNameEnd(MacroNode $node, PhpWriter $writer)
	{
		$tagName = strtolower($node->htmlNode->name);
		if ($tagName === 'form') {
			$node->innerContent .= '<?php echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false)'
				. " /* line $node->startLine */; ?>";
		} elseif ($tagName === 'label') {
			if ($node->htmlNode->empty) {
				$node->innerContent = "<?php echo \$ʟ_input->getLabelPart()->getHtml() /* line $node->startLine */; ?>";
			}
		} elseif ($tagName === 'button') {
			if ($node->htmlNode->empty) {
				$node->innerContent = $writer->write("<?php echo %escape(\$ʟ_input->getCaption()) /* line $node->startLine */; ?>");
			}
		} else { // select, textarea
			$node->innerContent = "<?php echo \$ʟ_input->getControl()->getHtml() /* line $node->startLine */; ?>";
		}
	}


	/**
	 * {inputError ...}
	 */
	public function macroInputError(MacroNode $node, PhpWriter $writer)
	{
		if ($node->modifiers) {
			throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
		}

		$name = $node->tokenizer->fetchWord();
		$node->replaced = true;
		if (!$name) {
			return $writer->write("echo %escape(\$ʟ_input->getError()) /* line $node->startLine */;");
		} elseif ($name[0] === '$') {
			return $writer->write(
				'$ʟ_input = is_object($ʟ_tmp = %0.word) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];'
				. "echo %escape(\$ʟ_input->getError()) /* line $node->startLine */;",
				$name
			);
		} else {
			return $writer->write("echo %escape(end(\$this->global->formsStack)[%0.word]->getError()) /* line $node->startLine */;", $name);
		}
	}


	/**
	 * {formPrint ClassName}
	 * {formClassPrint ClassName}
	 */
	public function macroFormPrint(MacroNode $node, PhpWriter $writer)
	{
		$name = $node->tokenizer->fetchWord();
		if ($name == null) { // null or false
			throw new CompileException('Missing form name in ' . $node->getNotation());
		}

		$node->tokenizer->reset();
		return $writer->write(
			'Nette\Bridges\FormsLatte\Runtime::render' . $node->name . '('
			. ($name[0] === '$'
				? 'is_object($ʟ_tmp = %node.word) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]'
				: '$this->global->uiControl[%node.word]')
			. '); exit;'
		);
	}
}
