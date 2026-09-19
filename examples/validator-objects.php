<?php declare(strict_types=1);

/**
 * Nette Forms validator objects example: configurable rules, a custom validator,
 * translation by stable message identifiers and correct plural forms.
 */


if (@!include __DIR__ . '/../vendor/autoload.php') { // @ dependencies may not be installed
	die('Install packages using `composer install`');
}

use Nette\Forms\{Form, Validation};
use Tracy\{Debugger, Dumper};

Debugger::enable();


// Custom rule as a configurable validator object: works on the server and
// exports itself for the client-side script (see Nette.validators below).
class ZipCodeValidator implements Validation\Validator
{
	public const InvalidZip = 'app.zipCode';


	public function __construct(
		private readonly bool $requireSpace = false,
	) {
	}


	public function validate(mixed $value): ?Validation\Message
	{
		$pattern = $this->requireSpace ? '\d{3} \d{2}' : '\d{3} ?\d{2}';
		return preg_match("~^$pattern$~", (string) $value)
			? null
			: new Validation\Message(self::InvalidZip, 'Please enter a valid ZIP code (e.g. 123 45).');
	}


	public function getClientRules(): array
	{
		return [new Validation\ClientRule(
			'appZipCode',
			arg: $this->requireSpace,
			message: new Validation\Message(self::InvalidZip, 'Please enter a valid ZIP code (e.g. 123 45).'),
		)];
	}
}


// Translator looking up Czech texts by the stable message identifier ($message->id),
// choosing the correct plural form by the count passed as the second argument.
// Unknown identifiers fall back to the default English text via (string) cast.
class CzechTranslator implements Nette\Localization\Translator
{
	private const Catalog = [
		'forms.filled' => 'Toto pole je povinné.',
		'forms.email' => 'Zadejte prosím platnou e-mailovou adresu.',
		'forms.length.min' => [
			'Zadejte prosím alespoň %count znak.',
			'Zadejte prosím alespoň %count znaky.',
			'Zadejte prosím alespoň %count znaků.',
		],
		'forms.length.max' => [
			'Zadejte prosím nejvýše %count znak.',
			'Zadejte prosím nejvýše %count znaky.',
			'Zadejte prosím nejvýše %count znaků.',
		],
		'app.zipCode' => 'Zadejte prosím platné PSČ (např. 123 45).',
		'Send' => 'Odeslat',
	];


	public function translate($message, ...$parameters): string|Stringable
	{
		$id = $message instanceof Validation\Message ? $message->id : (string) $message;
		$translation = self::Catalog[$id] ?? null;
		if ($translation === null) {
			return (string) $message; // fallback to the default English text
		}

		if (is_array($translation)) { // Czech plural forms: 1 / 2-4 / 5+
			$count = $parameters[0] ?? null;
			$translation = $translation[match (true) {
				$count === null, $count === 1 => 0,
				$count >= 2 && $count <= 4 => 1,
				default => 2,
			}];
		}

		return $translation;
	}
}


$form = new Form;
$form->setTranslator(new CzechTranslator);

$form->addText('zip', 'PSČ:')
	->setRequired()
	->addRule(new ZipCodeValidator);

$form->addText('email', 'E-mail:')
	->setRequired()
	->addRule(new Validation\EmailValidator);

// the same message id renders with the correct Czech plural form for each count
$form->addText('one', 'Nejvýše 1 znak:')
	->addRule(new Validation\LengthValidator(max: 1));

$form->addText('three', 'Alespoň 3 znaky:')
	->addRule(new Validation\LengthValidator(min: 3));

$form->addText('five', 'Alespoň 5 znaků:')
	->addRule(new Validation\LengthValidator(min: 5));

$form->addSubmit('submit', 'Send');


if ($form->isSuccess()) {
	echo '<h2>Form was submitted and successfully validated</h2>';
	Dumper::dump($form->getValues());
	exit;
}


?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>Nette Forms validator objects example</title>
<link rel="stylesheet" media="screen" href="assets/style.css" />
<script src="../src/assets/dist/nette-forms.umd.js"></script>

<script>
	// client-side counterpart of ZipCodeValidator; messages arrive already translated
	Nette.validators.appZipCode = function(elem, requireSpace, val) {
		return new RegExp('^\\d{3}' + (requireSpace ? ' ' : ' ?') + '\\d{2}$').test(val);
	};
</script>

<h1>Nette Forms validator objects example</h1>

<?php $form->render() ?>

<footer><a href="https://doc.nette.org/en/forms">see documentation</a></footer>
