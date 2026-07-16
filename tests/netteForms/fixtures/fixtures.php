<?php declare(strict_types=1);

use Nette\Bridges\FormsLatte\FormsExtension;
use Nette\Forms\{Container, Form};


/**
 * Renders the forms used by the end-to-end repeater specs.
 * @return array<string, string>  file name => HTML
 */
function createRepeaterFixtures(): array
{
	$form = new Form;
	$form->addRepeater('persons', function (Container $person) {
		$person->addHidden('id');
		$person->addText('name', 'Name')
			->setRequired()
			->setSuggestions(['Jim', 'Joe']);
		$person->addRepeater('emails', function (Container $email) use ($person) {
			$email->addEmail('email', 'Email')
				->addConditionOn($person['name'], Form::Filled)
				->setRequired();
		}, default: 1);
	}, min: 1, max: 3);
	$form->addSubmit('send', 'Send');

	$latte = new Latte\Engine;
	$latte->addExtension(new FormsExtension);
	$latte->setLoader(new Latte\Loaders\StringLoader);
	$latte->addProvider('uiControl', ['form' => $form]);
	$template = <<<'LATTE'
		{form form}
		{repeater persons}
			{label name /} {input name}
			{repeater emails}
				{label email /} {input email}
				<button n:repeater-remove>x</button>
			{/repeater}
			<button n:repeater-add="emails">Add email</button>
			<button n:repeater-remove>Remove person</button>
		{/repeater}
		<button n:repeater-add="persons">Add person</button>
		{input send}
		{/form}
		LATTE;
	$res['repeater-latte.html'] = $latte->renderToString($template);

	$named = new Form('order');
	$named->addRepeater('items', function (Container $item) {
		$item->addText('description', 'Description');
	}, default: 1);
	$named->addSubmit('send', 'Send');
	$res['repeater-renderer.html'] = (string) $named;

	$table = new Form('invoice');
	$table->addRepeater('rows', function (Container $row) {
		$row->addHidden('id');
		$row->addText('product')->setSuggestions(['Apple', 'Pear']);
	}, default: 1);
	$latte->addProvider('uiControl', ['form' => $table]);
	$tableTemplate = <<<'LATTE'
		{form form}
		<table>
			<tbody n:repeater=rows>
				<tr n:repeater-item>
					<td>{input product}</td>
					<td><button n:repeater-remove>x</button></td>
				</tr>
			</tbody>
		</table>
		<button n:repeater-add=rows>Add row</button>
		{/form}
		LATTE;
	$res['repeater-table.html'] = $latte->renderToString($tableTemplate);

	return $res;
}
