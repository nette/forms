<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte\CompileException;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Position;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {label ...} ... {/label}
 */
class LabelNode extends StatementNode
{
	public ExpressionNode $name;
	public ?ExpressionNode $part = null;
	public ArrayNode $attributes;
	public AreaNode $content;
	public bool $void;
	public ?Position $endLine;


	/** @return \Generator<int, ?array, array{AreaNode, ?Tag}, static|AreaNode> */
	public static function create(Tag $tag): \Generator
	{
		if ($tag->isNAttribute()) {
			throw new CompileException('Did you mean <label n:name=...> ?', $tag->position);
		}

		$tag->outputMode = $tag::OutputKeepIndentation;
		$tag->expectArguments();

		$node = new static;
		$node->name = $tag->parser->parseUnquotedStringOrExpression(colon: false);
		if ($tag->parser->stream->tryConsume(':') && !$tag->parser->stream->is(',')) {
			$node->part = $tag->parser->isEnd()
				? new StringNode('')
				: $tag->parser->parseUnquotedStringOrExpression();
		}

		$tag->parser->stream->tryConsume(',');
		$node->attributes = $tag->parser->parseArguments();
		$node->void = $tag->void;
		[$node->content, $endTag] = yield;
		$node->endLine = $endTag?->position;
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'echo ($ʟ_label = Nette\Bridges\FormsLatte\Runtime::item(%node, $this->global)->'
			. ($this->part ? 'getLabelPart(%node)' : 'getLabel()')
			. ')'
			. ($this->attributes->items ? '?->addAttributes(%2.node)' : '')
			. ($this->void ? ' %3.line;' : '?->startTag() %3.line; %4.node echo $ʟ_label?->endTag() %5.line;'),
			$this->name,
			$this->part,
			$this->attributes,
			$this->position,
			$this->content,
			$this->endLine,
		);
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
		if ($this->part) {
			yield $this->part;
		}
		yield $this->attributes;
		yield $this->content;
	}
}
