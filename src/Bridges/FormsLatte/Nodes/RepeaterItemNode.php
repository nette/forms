<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte\Compiler\Nodes\{AreaNode, AuxiliaryNode, FragmentNode, StatementNode};
use Latte\Compiler\{PrintContext, Tag};


/**
 * <tr n:repeater-item>: the element wrapping one repeater item, repeated for every item
 * and once inside the <template> element for the client side.
 */
final class RepeaterItemNode extends StatementNode
{
	public AreaNode $content;


	/** @return \Generator<int, ?list<string>, array{AreaNode, ?Tag}, static> */
	public static function create(Tag $tag): \Generator
	{
		$node = $tag->node = new static;
		[$node->content] = yield;
		$node->init($tag);
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			/** @lang InjectablePHP */
			<<<'XX'
				$ʟ_vars = get_defined_vars();
				echo $this->global->forms->currentRepeater()->renderEach(function ($ʟ_container) use ($ʟ_vars) {
					$this->global->forms->begin($ʟ_container, bindsForm: false);
					extract($ʟ_vars);
					%node
					$this->global->forms->end();
				});

				XX,
			$this->content,
		);
	}


	private function init(Tag $tag): void
	{
		$el = $tag->htmlElement;
		assert($el !== null);
		$tag->replaceNAttribute(new AuxiliaryNode(fn(PrintContext $context) => $context->format(
			'echo \' data-repeater-index="\', %escape($this->global->forms->getScope()->getName()), \'"\' %line;',
			$this->position,
		)));

		// hidden fields and datalists of the item belong inside the element, so they are cloned and removed with it
		$el->content = new FragmentNode([
			...($el->content ? [$el->content] : []),
			new AuxiliaryNode(fn(PrintContext $context) => $context->format(
				'echo $this->global->forms->currentRepeater()->renderItemEnd($this->global->forms->getScope()) %line;',
				$this->position,
			)),
		]);
	}


	public function &getIterator(): \Generator
	{
		yield $this->content;
	}
}
