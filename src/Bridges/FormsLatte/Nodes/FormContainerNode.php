<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {formContainer ...}
 */
class FormContainerNode extends StatementNode
{
	public ExpressionNode $name;
	public AreaNode $content;


	/** @return \Generator<int, ?array, array{AreaNode, ?Tag}, static|AreaNode> */
	public static function create(Tag $tag): \Generator
	{
		$tag->outputMode = $tag::OutputRemoveIndentation;
		$tag->expectArguments();

		$node = $tag->node = new static;
		$node->name = $tag->parser->parseUnquotedStringOrExpression();
		[$node->content] = yield;
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'$this->global->formsStack[] = $formContainer = Nette\Bridges\FormsLatte\Runtime::item(%node, $this->global) %line; '
			. '%node '
			. 'array_pop($this->global->formsStack); $formContainer = end($this->global->formsStack);'
			. "\n\n",
			$this->name,
			$this->position,
			$this->content,
		);
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
		yield $this->content;
	}
}
