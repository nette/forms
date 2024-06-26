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
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * <form n:name>
 */
final class FormNNameNode extends StatementNode
{
	public ExpressionNode $name;
	public AreaNode $content;


	public static function create(Tag $tag): \Generator
	{
		$tag->expectArguments();
		$node = $tag->node = new static;
		$node->name = $tag->parser->parseUnquotedStringOrExpression(colon: false);
		[$node->content] = yield;
		$node->init($tag);
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'$this->global->forms->begin($form = '
			. ($this->name instanceof StringNode
				? '$this->global->uiControl[%node]'
				: '(is_object($ʟ_tmp = %node) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp])')
			. ') %line;'
			. '%node '
			. '$this->global->forms->end();',
			$this->name,
			$this->position,
			$this->content,
		);
	}


	private function init(Tag $tag): void
	{
		$el = $tag->htmlElement;

		$tag->replaceNAttribute(new AuxiliaryNode(fn(PrintContext $context) => $context->format(
			'echo $this->global->forms->renderFormBegin(%dump, false) %line;',
			array_fill_keys(FieldNNameNode::findUsedAttributes($el), null),
			$this->position,
		)));

		$el->content = new Latte\Compiler\Nodes\FragmentNode([
			$el->content,
			new AuxiliaryNode(fn(PrintContext $context) => $context->format(
				'echo $this->global->forms->renderFormEnd(false) %line;',
				$this->position,
			)),
		]);
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
		yield $this->content;
	}
}
