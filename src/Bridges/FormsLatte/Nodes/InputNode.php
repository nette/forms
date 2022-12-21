<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {input ...}
 */
class InputNode extends StatementNode
{
	public ExpressionNode $name;
	public ?ExpressionNode $part = null;
	public ArrayNode $attributes;


	public static function create(Tag $tag): static
	{
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
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'echo Nette\Bridges\FormsLatte\Runtime::item(%node, $this->global)->'
			. ($this->part ? ('getControlPart(%node)') : 'getControl()')
			. ($this->attributes->items ? '->addAttributes(%2.node)' : '')
			. ' %3.line;',
			$this->name,
			$this->part,
			$this->attributes,
			$this->position,
		);
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
		if ($this->part) {
			yield $this->part;
		}
		yield $this->attributes;
	}
}
