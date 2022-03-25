<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\Nodes\Html\AttributeNode;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\NopNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * <form n:name>, <input n:name>, <select n:name>, <textarea n:name>, <label n:name> and <button n:name>
 */
final class NNameNode extends StatementNode
{
	public ExpressionNode $name;
	public ?ExpressionNode $part = null;
	public AreaNode $content;


	public static function create(Tag $tag): \Generator
	{
		$tag->expectArguments();
		$node = new static;
		$node->name = $tag->parser->parseUnquotedStringOrExpression(colon: false);
		if ($tag->parser->stream->tryConsume(':')) {
			$node->part = $tag->parser->isEnd()
				? new StringNode('')
				: $tag->parser->parseUnquotedStringOrExpression();
		}

		[$node->content] = yield;

		if (strtolower($tag->htmlElement->name) === 'form') {
			$node->initForm($tag);
		} else {
			$node->initElement($tag);
		}

		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $this->content->print($context);
	}


	private function initForm(Tag $tag)
	{
		$el = $tag->htmlElement;

		$tag->replaceNAttribute(new AuxiliaryNode(fn(PrintContext $context) => $context->format(
			'$form = $this->global->formsStack[] = '
			. ($this->name instanceof StringNode
				? '$this->global->uiControl[%node]'
				: 'is_object($ʟ_tmp = %node) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]')
			. ' %line;'
			. 'echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin(end($this->global->formsStack), %dump, false) %line;',
			$this->name,
			$this->position,
			array_fill_keys(self::findUsedAttributes($el), null),
			$this->position,
		)));

		$el->content = new Latte\Compiler\Nodes\FragmentNode([
			$el->content,
			new AuxiliaryNode(fn(PrintContext $context) => $context->format(
				'echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack), false) %line;',
				$this->position,
			)),
		]);
	}


	private function initElement(Tag $tag)
	{
		$el = $tag->htmlElement;
		$usedAttributes = self::findUsedAttributes($el);
		$elName = strtolower($el->name);

		$tag->replaceNAttribute(new AuxiliaryNode(fn(PrintContext $context) => $context->format(
			'$ʟ_input = '
			. ($this->name instanceof StringNode
				? 'end($this->global->formsStack)[%node];'
				: 'is_object($ʟ_tmp = %node) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];')
			. 'echo $ʟ_input->%raw(%node)'
			. ($usedAttributes ? '->addAttributes(%dump)' : '')
			. '->attributes() %4.line;',
			$this->name,
			$elName === 'label' ? 'getLabelPart' : 'getControlPart',
			$this->part,
			array_fill_keys($usedAttributes, null),
			$this->position,
		)));

		if ($elName === 'label') {
			if ($el->content instanceof NopNode) {
				$el->content = new AuxiliaryNode(fn(PrintContext $context) => $context->format(
					'echo $ʟ_input->getLabelPart()->getHtml() %line;',
					$this->position,
				));
			}
		} elseif ($elName === 'button') {
			if ($el->content instanceof NopNode) {
				$el->content = new AuxiliaryNode(fn(PrintContext $context) => $context->format(
					'echo %escape($ʟ_input->getCaption()) %line;',
					$this->position,
				));
			}
		} elseif ($el->content) { // select, textarea
			$el->content = new AuxiliaryNode(fn(PrintContext $context) => $context->format(
				'echo $ʟ_input->getControl()->getHtml() %line;',
				$this->position,
			));
		}
	}


	private static function findUsedAttributes(ElementNode $el): array
	{
		$res = [];
		foreach ($el->attributes?->children as $child) {
			if ($child instanceof AttributeNode && $child->name instanceof TextNode) {
				$res[] = $child->name->content;
			}
		}

		if (isset($el->nAttributes['class'])) {
			$res[] = 'class';
		}
		return $res;
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
		if ($this->part) {
			yield $this->part;
		}
		yield $this->content;
	}
}
