<?php declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\{Container, Form};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


$form = new Form('orderForm');
$items = $form->addRepeater('items', function (Container $item) {
	$item->addText('description');
	$item->addInteger('quantity');
});
$items->setBounds(min: 1, max: 5);

$tags = $form->addRepeater('tags', function (Container $tag) {
	$tag->addText('name');
});
$tags->setBounds(default: 1);

$form->addSubmit('send', 'Send');


$latte = new Latte\Engine;
$latte->addExtension(new FormsExtension);
$latte->addProvider('uiControl', ['myForm' => $form]);

Assert::matchFile(
	__DIR__ . '/expected/forms.repeater-n.html',
	$latte->renderToString(__DIR__ . '/templates/forms.repeater-n.latte'),
);


// n:repeater-item is allowed only inside the attribute form
$latte->setLoader(new Latte\Loaders\StringLoader);
Assert::exception(
	fn() => $latte->compile('{repeater items}<tr n:repeater-item></tr>{/repeater}'),
	Latte\CompileException::class,
	'Attribute n:repeater-item requires n:repeater, it cannot be used inside the {repeater} tag%a?%',
);
