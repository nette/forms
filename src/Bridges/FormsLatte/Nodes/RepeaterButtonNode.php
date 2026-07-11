<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\{PrintContext, Tag};


/**
 * n:repeater-add="..."
 * n:repeater-remove
 */
final class RepeaterButtonNode extends StatementNode
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
		return $this->name === 'add'
			? $context->format(
				'echo $this->global->forms->get(%node, Nette\Forms\Repeater::class)->getAddButtonPrototype()->attributes() %line;',
				$this->arg,
				$this->position,
			)
			: $context->format(
				'echo $this->global->forms->currentRepeater()->getRemoveButtonPrototype()->attributes() %line;',
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
