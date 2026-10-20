<?php declare(strict_types=1);

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte\Compiler\Nodes\{AreaNode, StatementNode};
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\{PrintContext, Tag};


/**
 * {formContainer name} ... {/formContainer}
 * Enters form container context for nested controls.
 */
class FormContainerNode extends StatementNode
{
	public ExpressionNode $name;
	public AreaNode $content;


	/** @return \Generator<int, ?list<string>, array{AreaNode, ?Tag}, static> */
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
			<<<'XX'
				$this->global->forms->begin($formContainer = $this->global->forms->get(%node, Nette\Forms\Container::class), bindsForm: false) %line;
				%node
				$this->global->forms->end(); $formContainer = $this->global->forms->getScope();

				XX,
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
