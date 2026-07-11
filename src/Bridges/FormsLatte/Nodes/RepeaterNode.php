<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte;
use Latte\Compiler\{Node, NodeTraverser, PrintContext, Tag};
use Latte\Compiler\Nodes\{AreaNode, AuxiliaryNode, StatementNode};
use Latte\Compiler\Nodes\Php\ExpressionNode;


/**
 * {repeater ...} and <tbody n:repeater=...>
 */
final class RepeaterNode extends StatementNode
{
	public ExpressionNode $name;
	public AreaNode $content;
	public bool $nAttribute = false;


	/** @return \Generator<int, ?list<string>, array{AreaNode, ?Tag}, static> */
	public static function create(Tag $tag): \Generator
	{
		$tag->outputMode = $tag::OutputRemoveIndentation;
		$tag->expectArguments();

		$node = $tag->node = new static;
		$node->name = $tag->parser->parseUnquotedStringOrExpression();
		[$node->content] = yield;
		if ($tag->isNAttribute()) {
			$node->nAttribute = true;
			$node->init($tag);
		} elseif ($node->containsRepeaterItem($node->content)) {
			throw new Latte\CompileException(
				'Attribute n:repeater-item requires n:repeater, it cannot be used inside the {repeater} tag.',
				$node->position,
			);
		}
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $this->nAttribute
			? $context->format(
				/** @lang InjectablePHP */
				<<<'XX'
					$ʟ_repeater = $this->global->forms->get(%node, Nette\Forms\Repeater::class);
					$this->global->forms->beginRepeater($ʟ_repeater);
					$ʟ_repeater->prepareForRendering();
					%node
					$this->global->forms->endRepeater();

					XX,
				$this->name,
				$this->content,
			)
			: $context->format(
				/** @lang InjectablePHP */
				<<<'XX'
					$ʟ_repeater = $this->global->forms->get(%node, Nette\Forms\Repeater::class);
					$this->global->forms->beginRepeater($ʟ_repeater);
					$ʟ_vars = get_defined_vars();
					echo $ʟ_repeater->render(function ($ʟ_container) use ($ʟ_vars) {
						$this->global->forms->begin($ʟ_container, bindsForm: false);
						extract($ʟ_vars);
						%node
						$this->global->forms->end();
					});
					$this->global->forms->endRepeater();

					XX,
				$this->name,
				$this->content,
			);
	}


	/**
	 * The written element becomes the repeater container; without an inner n:repeater-item
	 * its content is treated as the item recipe.
	 */
	private function init(Tag $tag): void
	{
		$el = $tag->htmlElement;
		assert($el !== null);

		$tag->replaceNAttribute(new AuxiliaryNode(fn(PrintContext $context) => $context->format(
			'echo Nette\Utils\Html::el(null)->addAttributes($ʟ_repeater->getContainerAttributes())->attributes() %line;',
			$this->position,
		)));

		if ($this->containsRepeaterItem($el->content)) {
			return;
		}

		$inner = $el->content;
		assert($inner !== null);
		$el->content = new AuxiliaryNode(fn(PrintContext $context) => $context->format(
			/** @lang InjectablePHP */
			<<<'XX'
				$ʟ_vars = get_defined_vars();
				echo $ʟ_repeater->renderItems(function ($ʟ_container) use ($ʟ_vars) {
					$this->global->forms->begin($ʟ_container, bindsForm: false);
					extract($ʟ_vars);
					%node
					$this->global->forms->end();
				});

				XX,
			$inner,
		));
	}


	private function containsRepeaterItem(?Node $node): bool
	{
		if ($node === null) {
			return false;
		}

		$found = false;
		(new NodeTraverser)->traverse($node, function (Node $node) use (&$found) {
			if ($node instanceof RepeaterItemNode) {
				$found = true;
				return NodeTraverser::StopTraversal;
			}
			return $node instanceof self
				? NodeTraverser::DontTraverseChildren // nested repeaters have their own items
				: null;
		});
		return $found;
	}


	public function &getIterator(): \Generator
	{
		yield $this->name;
		yield $this->content;
	}
}
