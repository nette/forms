<?php declare(strict_types=1);

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Bridges\FormsLatte\Nodes;

use Latte\Compiler\Nodes\{AreaNode, AuxiliaryNode, StatementNode};
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
				$ʟ_itemRenderer = function ($ʟ_container, $ʟ_itemIndex) use ($ʟ_vars) {
					$this->global->forms->begin($ʟ_container, bindsForm: false);
					extract($ʟ_vars);
					%node
					$this->global->forms->end();
				};
				foreach ($this->global->forms->currentRepeater()->getComponents() as $ʟ_index => $ʟ_container) {
					$ʟ_itemRenderer($ʟ_container, (string) $ʟ_index);
				}
				echo "<template>\n";
				$ʟ_itemRenderer($this->global->forms->currentRepeater()->createTemplate(), '');
				echo "</template>\n";

				XX,
			$this->content,
		);
	}


	private function init(Tag $tag): void
	{
		assert($tag->htmlElement !== null);
		$tag->replaceNAttribute(new AuxiliaryNode(fn(PrintContext $context) => $context->format(
			'echo \' data-repeater-index="\', %escape($ʟ_itemIndex), \'"\' %line;',
			$this->position,
		)));
	}


	public function &getIterator(): \Generator
	{
		yield $this->content;
	}
}
