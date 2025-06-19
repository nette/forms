<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\Bridges\FormsLatte;

use Latte;
use function strtolower;


/**
 * Latte v3 extension for Nette Forms.
 */
final class FormsExtension extends Latte\Extension
{
	public function getTags(): array
	{
		return [
			'form' => Nodes\FormNode::create(...),
			'formContext' => Nodes\FormNode::create(...),
			'formContainer' => Nodes\FormContainerNode::create(...),
			'label' => Nodes\LabelNode::create(...),
			'input' => Nodes\InputNode::create(...),
			'inputError' => Nodes\InputErrorNode::create(...),
			'formPrint' => Nodes\FormPrintNode::create(...),
			'formClassPrint' => Nodes\FormPrintNode::create(...),
			'n:name' => fn(Latte\Compiler\Tag $tag) => yield from strtolower($tag->htmlElement->name) === 'form'
				? Nodes\FormNNameNode::create($tag)
				: Nodes\FieldNNameNode::create($tag),
		];
	}


	public function getProviders(): array
	{
		return [
			'formsStack' => [],
		];
	}


	public function getCacheKey(Latte\Engine $engine): mixed
	{
		return 1;
	}
}
