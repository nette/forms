<?php declare(strict_types=1);

/**
 * Test: translating validation Messages by their stable identifier, with plurals
 */

use Nette\Forms\Controls\SelectBox;
use Nette\Forms\{Form, Repeater, Validation, Validator};
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


class CatalogTranslator implements Nette\Localization\Translator
{
	public array $log = [];


	public function __construct(
		private array $catalog = [],
	) {
	}


	public function translate($message, ...$parameters): string|Stringable
	{
		$id = $message instanceof Validation\Message ? $message->id : (string) $message;
		$count = $parameters[0] ?? null;
		$this->log[] = [$id, $count];

		$variants = (array) ($this->catalog[$id] ?? null);
		if (!$variants) {
			return (string) $message; // fallback to the default English text
		}

		// Czech plural rules: 1 / 2-4 / other
		$index = match (true) {
			$count === null, $count === 1 => 0,
			$count >= 2 && $count <= 4 => 1,
			default => 2,
		};
		return $variants[min($index, count($variants) - 1)];
	}
}


test('translator receives the Message object with a stable id and plural count', function () {
	$translator = new CatalogTranslator([
		'forms.length.min' => [
			'Zadejte alespoň %count znak.',
			'Zadejte alespoň %count znaky.',
			'Zadejte alespoň %count znaků.',
		],
	]);
	$form = new Form;
	$form->setTranslator($translator);

	$input = $form->addText('name')->setValue('ab');
	$input->addRule(new Validation\LengthValidator(min: 3));
	$input->validate();

	Assert::same(['Zadejte alespoň 3 znaky.'], $input->getErrors());
	Assert::contains(['forms.length.min', 3], $translator->log);
});


test('plural variant follows the count', function () {
	$catalog = [
		'forms.length.min' => [
			'Zadejte alespoň %count znak.',
			'Zadejte alespoň %count znaky.',
			'Zadejte alespoň %count znaků.',
		],
		'forms.length.max' => [
			'Zadejte nejvýše %count znak.',
			'Zadejte nejvýše %count znaky.',
			'Zadejte nejvýše %count znaků.',
		],
	];
	$cases = [
		[new Validation\LengthValidator(max: 1), 'xx', 'Zadejte nejvýše 1 znak.'],
		[new Validation\LengthValidator(min: 3), 'ab', 'Zadejte alespoň 3 znaky.'],
		[new Validation\LengthValidator(min: 5), 'abc', 'Zadejte alespoň 5 znaků.'],
	];

	foreach ($cases as [$validator, $value, $expected]) {
		$form = new Form;
		$form->setTranslator(new CatalogTranslator($catalog));
		$input = $form->addText('name')->setValue($value);
		$input->addRule($validator);
		$input->validate();
		Assert::same([$expected], $input->getErrors());
	}
});


test('unknown id falls back to the default English text via string cast', function () {
	$form = new Form;
	$form->setTranslator(new CatalogTranslator);
	$input = $form->addText('email')->setValue('wrong');
	$input->addRule(new Validation\EmailValidator);
	$input->validate();
	Assert::same(['Please enter a valid email address.'], $input->getErrors());
});


test('select box message translates by its stable id', function () {
	$form = new Form;
	$form->setTranslator(new CatalogTranslator([
		SelectBox::Valid => 'Vyberte prosím platnou položku.',
	]));
	$select = $form->addSelect('s', null, ['a' => 'A']);
	$select->validate();
	Assert::same(['Vyberte prosím platnou položku.'], array_map(strval(...), $select->getErrors()));
});


test('late override of the select box default message applies (lazy resolution)', function () {
	$form = new Form;
	$select = $form->addSelect('s', null, ['a' => 'A']);
	Validator::$messages[SelectBox::Valid] = 'Choose something!';
	try {
		$select->validate();
		Assert::same(['Choose something!'], array_map(strval(...), $select->getErrors()));
	} finally {
		Validator::$messages = [];
	}
});


test('formatStandalone: id translation with plurals, English fallback, %d overrides', function () {
	$message = new Validation\Message(Repeater::MinItems, 'Please add at least %count items.', count: 3);

	$plural = new CatalogTranslator([
		Repeater::MinItems => [
			'Přidejte alespoň %count položku.',
			'Přidejte alespoň %count položky.',
			'Přidejte alespoň %count položek.',
		],
	]);
	Assert::same('Přidejte alespoň 3 položky.', Nette\Forms\MessageFormatter::formatStandalone($message, $plural));
	Assert::same('Please add at least 3 items.', Nette\Forms\MessageFormatter::formatStandalone($message, null));

	Validator::$messages[Repeater::MinItems] = 'At least %d pieces.'; // legacy %d placeholder keeps working
	try {
		Assert::same('At least 3 pieces.', Nette\Forms\MessageFormatter::formatStandalone($message, null));
	} finally {
		Validator::$messages = [];
	}
});


test('catalogs keyed by English text keep working through the cast', function () {
	$textTranslator = new class implements Nette\Localization\Translator {
		public function translate($message, ...$parameters): string
		{
			$table = ['Please enter a valid email address.' => 'Zadejte platný e-mail.'];
			return $table[(string) $message] ?? (string) $message;
		}
	};

	$form = new Form;
	$form->setTranslator($textTranslator);
	$input = $form->addText('email')->setValue('wrong');
	$input->addRule(new Validation\EmailValidator);
	$input->validate();
	Assert::same(['Zadejte platný e-mail.'], $input->getErrors());
});


test('text-keyed translator receives the Message with the unsubstituted template', function () {
	$translator = new class implements Nette\Localization\Translator {
		public array $received = [];


		public function translate($message, ...$parameters): string
		{
			$this->received[] = $message;
			$table = ['Please enter at least %count characters.' => 'Zadejte alespoň %count znaků.'];
			return $table[(string) $message] ?? (string) $message;
		}
	};

	$form = new Form;
	$form->setTranslator($translator);
	$input = $form->addText('name')->setValue('ab');
	$input->addRule(Form::MinLength, null, 5);
	$input->validate();

	Assert::same(['Zadejte alespoň 5 znaků.'], $input->getErrors());
	$message = $translator->received[0];
	Assert::type(Validation\Message::class, $message);
	Assert::same('forms.length.min', $message->id);
	Assert::same(5, $message->count);
	Assert::same('Please enter at least %count characters.', (string) $message);
});


test('override by message id applies to both declaration styles', function () {
	Validator::$messages['forms.length.min'] = 'Aspoň %count znaků';
	try {
		$form = new Form;
		$byOp = $form->addText('a')->setValue('x')->addRule(Form::MinLength, null, 3);
		$byObject = $form->addText('b')->setValue('x')->addRule(new Validation\LengthValidator(min: 3));
		$form->validate();
		Assert::same(['Aspoň 3 znaků'], $byOp->getErrors());
		Assert::same(['Aspoň 3 znaků'], $byObject->getErrors());
	} finally {
		Validator::$messages = [];
	}
});


test('override by operation applies to both declaration styles, including positional placeholders', function () {
	Validator::$messages[Form::Email] = 'Bad e-mail';
	Validator::$messages[Form::MinLength] = 'At least %d characters';
	try {
		$form = new Form;
		$emailOp = $form->addText('a')->setValue('x')->addRule(Form::Email);
		$emailObject = $form->addText('b')->setValue('x')->addRule(new Validation\EmailValidator);
		$lengthOp = $form->addText('c')->setValue('x')->addRule(Form::MinLength, null, 3);
		$lengthObject = $form->addText('d')->setValue('x')->addRule(new Validation\LengthValidator(min: 3));
		$form->validate();
		Assert::same(['Bad e-mail'], $emailOp->getErrors());
		Assert::same(['Bad e-mail'], $emailObject->getErrors());
		Assert::same(['At least 3 characters'], $lengthOp->getErrors());
		Assert::same(['At least 3 characters'], $lengthObject->getErrors());
	} finally {
		Validator::$messages = [];
	}
});


test('override by id wins over override by operation, message given to the rule wins over both', function () {
	Validator::$messages[Form::Email] = 'by operation';
	Validator::$messages['forms.email'] = 'by id';
	try {
		$form = new Form;
		$plain = $form->addText('a')->setValue('x')->addRule(Form::Email);
		$own = $form->addText('b')->setValue('x')->addRule(Form::Email, 'own');
		$form->validate();
		Assert::same(['by id'], $plain->getErrors());
		Assert::same(['own'], $own->getErrors());
	} finally {
		Validator::$messages = [];
	}
});


test('override by operation does not hijack a message with an own id', function () {
	Validator::$messages[Form::Filled] = 'Required!';
	try {
		$form = new Form;
		$select = $form->addSelect('s', null, ['a' => 'A']);
		$select->validate();
		Assert::same(['Please select a valid option.'], $select->getErrors());
	} finally {
		Validator::$messages = [];
	}
});


test('overrides are empty by default, the defaults live with the validators', function () {
	Assert::same([], Validator::$messages);
	$form = new Form;
	$input = $form->addText('a')->setValue('x')->addRule(Form::Numeric);
	$input->validate();
	Assert::same(['Please enter a non-negative integer.'], $input->getErrors());
});


test('override and translation of standalone messages', function () {
	$message = new Validation\Message(Form::MaxPostSize, 'The uploaded data exceeds the limit of %limit bytes.', ['limit' => 100]);
	Assert::same('The uploaded data exceeds the limit of 100 bytes.', Nette\Forms\MessageFormatter::formatStandalone($message, null));

	Validator::$messages[Form::MaxPostSize] = 'Max %d bytes, %label stays';
	try {
		Assert::same('Max 100 bytes, %label stays', Nette\Forms\MessageFormatter::formatStandalone($message, null));
	} finally {
		Validator::$messages = [];
	}
});
