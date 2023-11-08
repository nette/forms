<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte\Nodes;

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
 * <input n:name>, <select n:name>, <textarea n:name>, <label n:name> and <button n:name>
 */
final class FieldNNameNode extends StatementNode
{
	public ExpressionNode $name;
	public ?ExpressionNode $part = null;
	public AreaNode $content;


	public static function create(Tag $tag): \Generator
	{
		$tag->expectArguments();
		$node = $tag->node = new static;
		$node->name = $tag->parser->parseUnquotedStringOrExpression(colon: false);
		if ($tag->parser->stream->tryConsume(':')) {
			$node->part = $tag->parser->isEnd()
				? new StringNode('')
				: $tag->parser->parseUnquotedStringOrExpression();
		}

		[$node->content] = yield;

		$node->init($tag);

		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $this->content->print($context);
	}


	private function init(Tag $tag)
	{
		$el = $tag->htmlElement;
		$usedAttributes = self::findUsedAttributes($el);
		$elName = strtolower($el->name);

		$tag->replaceNAttribute(new AuxiliaryNode(fn(PrintContext $context) => $context->format(
			'echo ($ʟ_elem = Nette\Bridges\FormsLatte\Runtime::item(%node, $this->global)'
			. ($elName === 'label' ? '->getLabelPart(%node))' : '->getControlPart(%node))')
			. ($usedAttributes ? '->addAttributes(%dump)' : '')
			. '->attributes() %3.line;',
			$this->name,
			$this->part,
			array_fill_keys($usedAttributes, null),
			$this->position,
		)));

		if ($elName === 'label') {
			if ($el->content instanceof NopNode) {
				$el->content = new AuxiliaryNode(fn(PrintContext $context) => $context->format(
					'echo $ʟ_elem->getHtml() %line;',
					$this->position,
				));
			}
		} elseif ($elName === 'button') {
			if ($el->content instanceof NopNode) {
				$el->content = new AuxiliaryNode(fn(PrintContext $context) => $context->format(
					'echo %escape($ʟ_elem->value) %line;',
					$this->position,
				));
			}
		} elseif ($el->content) { // select, textarea
			$el->content = new AuxiliaryNode(fn(PrintContext $context) => $context->format(
				'echo $ʟ_elem->getHtml() %line;',
				$this->position,
			));
		}
	}


	/** @internal */
	public static function findUsedAttributes(ElementNode $el): array
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
