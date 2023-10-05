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
 * {form name} ... {/form}
 * {formContext ...}
 */
class FormNode extends StatementNode
{
	public ExpressionNode $name;
	public ArrayNode $attributes;
	public AreaNode $content;
	public bool $print;
	public ?Position $endLine;


	/** @return \Generator<int, ?array, array{AreaNode, ?Tag}, static|AreaNode> */
	public static function create(Tag $tag): \Generator
	{
		if ($tag->isNAttribute()) {
			throw new CompileException('Did you mean <form n:name=...> ?', $tag->position);
		}

		$tag->outputMode = $tag::OutputKeepIndentation;
		$tag->expectArguments();
		$node = new static;
		$node->name = $tag->parser->parseUnquotedStringOrExpression();
		$tag->parser->stream->tryConsume(',');
		$node->attributes = $tag->parser->parseArguments();
		$node->print = $tag->name === 'form';

		[$node->content, $endTag] = yield;
		$node->endLine = $endTag?->position;
		if ($endTag && $node->name instanceof StringNode) {
			$endTag->parser->stream->tryConsume($node->name->value);
		}

		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'$form = $this->global->formsStack[] = '
			. ($this->name instanceof StringNode
				? '$this->global->uiControl[%node]'
				: 'is_object($ʟ_tmp = %node) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]')
			. ' %line;'
			. 'Nette\Bridges\FormsLatte\Runtime::initializeForm($form);'
			. ($this->print
				? 'echo Nette\Bridges\FormsLatte\Runtime::renderFormBegin($form, %node) %1.line;'
				: '')
			. ' %3.node '
			. ($this->print
				? 'echo Nette\Bridges\FormsLatte\Runtime::renderFormEnd(array_pop($this->global->formsStack))'
				: 'array_pop($this->global->formsStack)')
			. " %4.line;\n\n",
			$this->name,
			$this->position,
			$this->attributes,
			$this->content,
			$this->endLine,
		);
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
		yield $this->attributes;
		yield $this->content;
	}
}
