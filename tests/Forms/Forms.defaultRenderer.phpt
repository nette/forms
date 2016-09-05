<?php

/**
 * Test: Nette\Forms HTTP data.
 */

use Nette\Forms\Form;
use Tester\Assert;


require __DIR__ . '/../bootstrap.php';

class FakeRenderer implements \Nette\Forms\IFormRenderer {
	public static $called = FALSE;
	public function render(Form $form)
	{
		self::$called = TRUE;
	}
}

Form::$defaultRenderer = FakeRenderer::class;
$form = new Form;
$form->render();
Assert::true(FakeRenderer::$called);
