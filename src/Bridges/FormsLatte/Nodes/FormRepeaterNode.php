<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {formRepeater ...}
 */
final class FormRepeaterNode extends StatementNode
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
			/** @lang InjectablePHP */
			<<<'XX'
				$ʟ_repeater = $this->global->forms->item(%node, Nette\Forms\Repeater::class);
				$ʟ_vars = get_defined_vars();
				$ʟ_repeater->render(function ($ʟ_container) use ($ʟ_vars) {
					$this->global->forms->begin($ʟ_container);
					extract($ʟ_vars);
					%node
					$this->global->forms->end();
				});

				XX,
			$this->name,
			$this->content,
		);
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
		yield $this->content;
	}
}
