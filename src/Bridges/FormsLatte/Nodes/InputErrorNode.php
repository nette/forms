<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {inputError ...}
 */
class InputErrorNode extends StatementNode
{
	public ExpressionNode $name;


	public static function create(Tag $tag): static
	{
		$tag->outputMode = $tag::OutputKeepIndentation;
		$tag->expectArguments();

		$node = new static;
		$node->name = $tag->parser->parseUnquotedStringOrExpression();
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'echo %escape(Nette\Bridges\FormsLatte\Runtime::item(%node, $this->global)->getError()) %line;',
			$this->name,
			$this->position,
		);
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
	}
}
