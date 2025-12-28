# Repeater - Dynamic Form Containers

Repeater umožňuje vytvářet dynamické kontejnery ve formulářích, které lze přidávat a odebírat na straně klienta pomocí JavaScriptu.

## Základní použití

```php
use Nette\Forms\Form;

$form = new Form;

// Přidání repeateru
$persons = $form->addRepeater('persons', function (Nette\Forms\Container $person) {
    $person->addText('firstname', 'Jméno:')->setRequired();
    $person->addText('surname', 'Příjmení:')->setRequired();
});

// Konfigurace počtu položek
$persons->setBounds(min: 1, max: 5, default: 2);

// Tlačítka pro UI (volitelné)
$persons->addCreateButton('Přidat osobu')
    ->setHtmlAttribute('class', 'btn btn-success');

$persons->addRemoveButton('Odebrat')
    ->setHtmlAttribute('class', 'btn btn-danger');
```

## Latte šablona

```latte
{form myForm}
    {formRepeater persons}
        <div class="person-item">
            <label>
                Jméno: {input firstname}
            </label>
            <label>
                Příjmení: {input surname}
            </label>

            {* Remove button *}
            <button n:repeater-remove>Odebrat</button>
        </div>
    {/formRepeater}

    {* Create button *}
    <button n:repeater-add="persons">Přidat osobu</button>

    {input submit}
{/form}
```

## Vnořené repeatery

```php
$persons = $form->addRepeater('persons', function (Container $person) {
    $person->addText('firstname', 'Jméno:');

    // Vnořený repeater pro emaily
    $person->addRepeater('emails', function (Container $email) {
        $email->addEmail('email', 'Email:');
    })->setBounds(min: 1, max: 3);
});
```

```latte
{formRepeater persons}
    {input firstname}

    {formRepeater emails}
        {input email}
        <button n:repeater-remove>×</button>
    {/formRepeater}

    <button n:repeater-add="emails">+ email</button>
{/formRepeater}
```

## API

### `addRepeater(string $name, callable $factory): Repeater`

Přidá repeater do formuláře nebo kontejneru.

**Parametry:**
- `$name` - název repeateru
- `$factory` - callable, který vytvoří strukturu jednoho itemu

```php
$form->addRepeater('items', function (Container $item) {
    $item->addText('name', 'Název:');
    $item->addInteger('quantity', 'Počet:');
});
```

### `setBounds(int $min = 0, ?int $max = null, ?int $default = null): static`

Nastaví minimální, maximální a výchozí počet položek.

**Parametry:**
- `$min` - minimální počet položek (výchozí 0)
- `$max` - maximální počet položek (null = neomezeno)
- `$default` - výchozí počet položek (výchozí = min)

```php
$repeater->setBounds(min: 1, max: 10, default: 3);
```

### `addCreateButton(string|Stringable $caption): Html`

Přidá konfiguraci pro tlačítko "Přidat položku". Vrací Html element pro nastavení atributů.

```php
$repeater->addCreateButton('Přidat')
    ->setHtmlAttribute('class', 'btn btn-primary')
    ->setHtmlAttribute('data-custom', 'value');
```

### `addRemoveButton(string|Stringable $caption): Html`

Přidá konfiguraci pro tlačítko "Odebrat položku". Vrací Html element pro nastavení atributů.

```php
$repeater->addRemoveButton('×')
    ->setHtmlAttribute('class', 'btn btn-sm btn-danger');
```

### `getControlPart(?string $name): ?Html`

Vrací virtuální control part pro tlačítka.

```php
// V Latte:
<button n:repeater-remove>Odebrat</button>
<button n:repeater-add="persons">Přidat osobu</button>
```

## Nastavení výchozích hodnot

```php
$form->setDefaults([
    'persons' => [
        [
            'firstname' => 'Jan',
            'surname' => 'Novák',
        ],
        [
            'firstname' => 'Marie',
            'surname' => 'Nováková',
        ],
    ],
]);
```

## Získání hodnot

```php
if ($form->isSuccess()) {
    $values = $form->getValues();
    // $values->persons je pole objektů/polí s daty jednotlivých položek

    foreach ($values->persons as $person) {
        echo $person->firstname . ' ' . $person->surname;
    }
}
```

## Validace

Repeater automaticky přidává validační pravidla:

```php
$repeater->setBounds(min: 2, max: 5);
// Automaticky přidá: Form::Count s rozsahem [2, 5]
```

## JavaScript

Pro funkčnost přidávání/odebírání položek je potřeba JavaScript. Použijte připravený soubor:

```html
<script src="path/to/repeater.js"></script>
```

Soubor se nachází v `src/assets/repeater.js` a obsahuje třídu `RepeaterManager`, která automaticky inicializuje všechny repeatery na stránce.

### Funkce

**Automatická detekce změn:** Při pokusu o odstranění položky s upravenými hodnotami se uživatel automaticky dotáže na potvrzení. RepeaterManager porovnává aktuální hodnoty všech formulářových prvků s jejich výchozími hodnotami (ty, které by se obnovily po `reset()`).

**Podpora min/max limitů:** JavaScript automaticky kontroluje min/max atributy na repeateru a zabraňuje přidání/odebrání položek mimo tyto limity.

### Manuální inicializace

Pokud potřebujete inicializovat repeatery manuálně (např. po AJAXovém načtení):

```javascript
const manager = new RepeaterManager();
// nebo inicializovat jen v konkrétním kontextu
manager.init(document.getElementById('my-container'));
```

Viz kompletní příklad v `examples/repeater-template.latte`.

### Testování

JavaScript testy pomocí Karma/Jasmine se nacházejí v `tests/netteForms/spec/repeaterSpec.js`.

## HTML struktura

Repeater generuje následující HTML:

```html
<div data-nette-repeater="persons">
    <!-- Existující položky -->
    <div data-repeater-index="0">
        <input name="persons[0][firstname]" id="frm-persons-0-firstname">
        <!-- ... -->
    </div>

    <div data-repeater-index="1">
        <input name="persons[1][firstname]" id="frm-persons-1-firstname">
        <!-- ... -->
    </div>

    <!-- Template pro nové položky -->
    <template
        id="frm-persons-template"
        data-nette-repeater-path="persons[0]">
        <input name="persons[0][firstname]" id="frm-persons-0-firstname">
        <!-- ... -->
    </template>
</div>
```

## Data atributy

- `data-nette-repeater="name"` - označuje repeater kontejner
- `data-repeater-index="0"` - index položky (na wrapper elementu)
- `data-nette-repeater-create="name"` - cílový repeater pro create button
- `data-nette-repeater-max="5"` - maximální počet položek
- `data-nette-repeater-min="1"` - minimální počet položek

## Registrace Latte Extension

V bootstrapu nebo konfiguraci:

```php
$latte->addExtension(new Nette\Bridges\FormsLatte\FormsExtension);
```

Nebo v `common.neon`:

```neon
latte:
    extensions:
        - Nette\Bridges\FormsLatte\FormsExtension
```

## Známá omezení

- JavaScript pro přidávání/odebírání je třeba implementovat samostatně
- Indexy v HTTP datech musí být numerické
- Při odeslání formuláře se items renumerují na souvislou řadu od 0

## Viz také

- `examples/repeater-example.php` - základní příklad
- `examples/repeater-template.latte` - příklad s Latte a JavaScriptem
