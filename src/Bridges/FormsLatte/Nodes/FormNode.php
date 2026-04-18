<?php declare(strict_types=1);

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte\CompileException;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {form name [, attributes]} ... {/form}
 * {formContext name} ... {/formContext}
 * Renders form tags and initializes form context.
 */
class FormNode extends StatementNode
{
	public ExpressionNode $name;
	public ArrayNode $attributes;
	public AreaNode $content;
	public bool $print;


	/** @return \Generator<int, ?list<string>, array{AreaNode, ?Tag}, static> */
	public static function create(Tag $tag): \Generator
	{
		if ($tag->isNAttribute()) {
			throw new CompileException('Did you mean <form n:name=...> ?', $tag->position);
		}

		$tag->outputMode = $tag::OutputKeepIndentation;
		$tag->expectArguments();
		$node = $tag->node = new static;
		$node->name = $tag->parser->parseUnquotedStringOrExpression();
		if (!$tag->parser->stream->tryConsume(',') && !$tag->parser->isEnd()) {
			$position = $tag->parser->stream->peek()->position;
			trigger_error("Missing comma before arguments in {{$tag->name}} tag $position.", E_USER_DEPRECATED);
		}
		$node->attributes = $tag->parser->parseArguments();
		$node->print = $tag->name === 'form';

		[$node->content, $endTag] = yield;
		if ($endTag && $node->name instanceof StringNode) {
			$endTag->parser->stream->tryConsume($node->name->value);
		}

		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'$this->global->forms->begin($form = '
			. ($this->name instanceof StringNode
				? '$this->global->uiControl[%node]'
				: '(is_object($ʟ_tmp = %node) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp])')
			. ', global: $this->global) %line;'
			. ($this->print
				? 'echo $this->global->forms->renderFormBegin(%node) %1.line;'
				: '')
			. ' %3.node '
			. ($this->print
				? 'echo $this->global->forms->renderFormEnd() %4.line;'
				: '')
			. '$this->global->forms->end();'
			. "\n\n",
			$this->name,
			$this->position,
			$this->attributes,
			$this->content,
			end($this->tagRanges),
		);
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
		yield $this->attributes;
		yield $this->content;
	}
}
