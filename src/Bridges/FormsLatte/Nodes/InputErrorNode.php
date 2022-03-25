<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {inputError ...}
 */
class InputErrorNode extends StatementNode
{
	public ?ExpressionNode $name;


	public static function create(Tag $tag): static
	{
		$tag->outputMode = $tag::OutputKeepIndentation;
		$node = new static;
		$node->name = $tag->parser->isEnd()
			? null
			: $tag->parser->parseUnquotedStringOrExpression();
		return $node;
	}


	public function print(PrintContext $context): string
	{
		if (!$this->name) {
			return $context->format('echo %escape($ʟ_input->getError()) %line;', $this->position);

		} elseif ($this->name instanceof StringNode) {
			return $context->format(
				'echo %escape(end($this->global->formsStack)[%node]->getError()) %line;',
				$this->name,
				$this->position,
			);

		} else {
			return $context->format(
				'$ʟ_input = is_object($ʟ_tmp = %node) ? $ʟ_tmp : end($this->global->formsStack)[$ʟ_tmp];'
				. 'echo %escape($ʟ_input->getError()) %line;',
				$this->name,
				$this->position,
			);
		}
	}


	public function &getIterator(): \Generator
	{
		if ($this->name) {
			yield $this->name;
		}
	}
}
