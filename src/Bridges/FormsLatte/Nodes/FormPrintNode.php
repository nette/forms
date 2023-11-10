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
 * {formPrint [ClassName]}
 * {formClassPrint [ClassName]}
 */
class FormPrintNode extends StatementNode
{
	public ?ExpressionNode $name;
	public string $mode;


	public static function create(Tag $tag): static
	{
		$node = new static;
		$node->name = $tag->parser->isEnd()
			? null
			: $tag->parser->parseUnquotedStringOrExpression();
		$node->mode = $tag->name;
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'Nette\Forms\Blueprint::%raw('
			. ($this->name
				? 'is_object($ʟ_tmp = %node) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]'
				: 'end($this->global->formsStack)')
			. ') %2.line; exit;',
			$this->mode === 'formPrint' ? 'latte' : 'dataClass',
			$this->name,
			$this->position,
		);
	}


	public function &getIterator(): \Generator
	{
		if ($this->name) {
			yield $this->name;
		}
	}
}
