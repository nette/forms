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
 * n:repeater-add="..."
 * n:repeater-remove
 */
final class FormRepeaterButtonNode extends StatementNode
{
	public string $name; // add|remove
	public ?ExpressionNode $arg = null;


	public static function create(Tag $tag): static
	{
		$node = new static;
		$node->name = str_replace('repeater-', '', $tag->name);
		if ($node->name === 'add') {
			$tag->expectArguments();
			$node->arg = $tag->parser->parseUnquotedStringOrExpression();
		}
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'echo ('
			. ($this->arg
				? '$this->global->forms->item(%node, Nette\Forms\Repeater::class)'
				: '$ʟ_repeater') // TODO
			//	: '$this->global->forms->current()')
			. ')->getButtonControl(%1.dump)->attributes() %2.line;',
			$this->arg,
			$this->name,
			$this->position,
		);
	}


	public function &getIterator(): \Generator
	{
		if ($this->arg) {
			yield $this->arg;
		}
	}
}
