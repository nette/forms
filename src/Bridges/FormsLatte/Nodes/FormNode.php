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
use function in_array;


/**
 * {form [scope|detached] name [, attributes]} ... {/form}
 * {formContext name} ... {/formContext}
 * Renders form tags and initializes form context.
 */
class FormNode extends StatementNode
{
	private const ModeLegacyScope = 'context';
	private const ModeScope = 'scope';
	private const ModeDetached = 'detached';

	public ExpressionNode $name;
	public ArrayNode $attributes;
	public AreaNode $content;
	public ?string $mode = null;


	/** @return \Generator<int, ?list<string>, array{AreaNode, ?Tag}, static> */
	public static function create(Tag $tag): \Generator
	{
		if ($tag->isNAttribute()) {
			throw new CompileException('Did you mean <form n:name=...> ?', $tag->position);
		}

		$tag->outputMode = $tag::OutputKeepIndentation;
		$tag->expectArguments();
		$node = $tag->node = new static;
		$node->mode = match (true) {
			$tag->name === 'formContext' => self::ModeLegacyScope,
			in_array($tag->parser->stream->tryPeek()?->text, [self::ModeScope, self::ModeDetached], strict: true) => $tag->parser->stream->consume()->text,
			default => null,
		};
		$node->name = $tag->parser->parseUnquotedStringOrExpression();
		if (!$tag->parser->stream->tryConsume(',') && !$tag->parser->isEnd()) {
			$position = $tag->parser->stream->peek()->position;
			trigger_error("Missing comma before arguments in {{$tag->name}} tag $position.", E_USER_DEPRECATED);
		}
		$node->attributes = $tag->parser->parseArguments();
		if ($node->mode !== null && $node->mode !== self::ModeDetached && $node->attributes->items) {
			$label = '{' . $tag->name . ($node->mode === self::ModeScope ? ' scope' : '') . '}';
			throw new CompileException("Arguments are not allowed in $label because it does not render a <form> tag.", $tag->position);
		}

		[$node->content, $endTag] = yield;
		if ($endTag && $node->name instanceof StringNode) {
			$endTag->parser->stream->tryConsume($node->name->value);
		}

		return $node;
	}


	public function print(PrintContext $context): string
	{
		$renderBegin = 'echo $this->global->forms->renderFormBegin(%node) %1.line;';
		$renderEnd = 'echo $this->global->forms->renderFormEnd() %4.line;';

		return $context->format(
			'$this->global->forms->begin($form = '
			. (match (true) {
				$this->mode === self::ModeScope => '(is_object($ʟ_tmp = %node) ? $ʟ_tmp : ($this->global->forms->isNested() ? $this->global->forms->get($ʟ_tmp, Nette\Forms\Container::class) : $this->global->uiControl[$ʟ_tmp]))',
				$this->name instanceof StringNode => '$this->global->uiControl[%node]',
				default => '(is_object($ʟ_tmp = %node) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp])',
			})
			. ($this->mode === self::ModeDetached ? ', detached: true' : '')
			. ', global: $this->global) %line;'
			. (match ($this->mode) {
				self::ModeScope, self::ModeLegacyScope => ' %3.node ',
				self::ModeDetached => $renderBegin . $renderEnd . ' %3.node ',
				default => $renderBegin . ' %3.node ' . $renderEnd,
			})
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
