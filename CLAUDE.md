# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Nette Forms is a mature PHP library (since 2004) for creating, validating, and processing web forms with both server-side (PHP) and client-side (JavaScript) validation. Part of the Nette Framework ecosystem.

- **PHP Requirements:** 8.2 - 8.5
- **Dependencies:** nette/component-model, nette/http, nette/utils
- **Latte Integration:** Requires Latte 3.1+ (conflict with < 3.1 or >= 3.2)
- **Current Branch:** v3.3-dev

## Essential Commands

### PHP Development

```bash
# Install dependencies
composer install

# Run all tests
composer run tester
# Or directly:
vendor/bin/tester tests -s -C

# Run tests in specific directory
vendor/bin/tester tests/Forms/ -s -C

# Run single test file
php tests/Forms/Form.render.phpt

# Static analysis
composer run phpstan
```

### JavaScript Development

```bash
# Install dependencies
npm install

# Build JavaScript assets (UMD + minified + types)
npm run build

# Run JavaScript tests (Vitest)
npm run test
npm run test:watch    # Watch mode
npm run test:ui       # UI mode

# Type checking
npm run typecheck

# Linting
npm run lint
npm run lint:fix
```

**Build Output:** `src/assets/netteForms.js`, `netteForms.min.js`, `netteForms.d.ts`

## Architecture Overview

### Core PHP Structure

**Class Hierarchy:**
- `Form` (extends `Container`) - Main entry point for form creation
- `Container` - Holds controls and nested containers
- `Control` (interface) - Contract for all form controls
- `BaseControl` (abstract) - Base implementation for controls

**Form Controls** (19 types in `src/Forms/Controls/`):
- Text inputs: `TextInput`, `TextArea`, `EmailControl`, `PasswordInput`
- Choice controls: `SelectBox`, `RadioList`, `CheckboxList`, `MultiSelectBox`
- Special: `Button`, `SubmitButton`, `ImageButton`, `Checkbox`, `HiddenField`, `ColorPicker`, `DateTimeControl`, `UploadControl`

**Validation System:**
- `Rules` - Manages validation rules per control
- `Rule` - Value object for single validation rule
- `Validator` - Built-in validators (email, URL, range, file size, etc.)
- Supports conditional rules and custom validators

**Rendering:**
- `FormRenderer` (interface) - Rendering contract
- `DefaultFormRenderer` - Default HTML output
- Multiple strategies supported (Bootstrap 4/5, custom)

### Bridge Integrations

**`Bridges/FormsDI/`** - Nette DI container extension
- `FormsExtension` - DI integration for forms

**`Bridges/FormsLatte/`** - Latte 3.1+ templating integration
- `FormsExtension` - Adds Latte tags: `{form}`, `{input}`, `{label}`, `{inputError}`, `{formContainer}`, `{formPrint}`
- `Runtime` - Non-static runtime class (recently refactored from static)
- `Nodes/` - Latte compiler nodes for template processing

### JavaScript Architecture

**Source:** `src/assets/` (TypeScript)
- `formValidator.ts` - Main validation orchestrator
- `validators.ts` - Collection of validation functions
- `types.ts` - TypeScript type definitions
- `webalize.ts` - String utilities

**Build System:** Rollup with custom transformations
- Converts spaces to tabs (project standard)
- Adds header comment
- Generates UMD module with auto-init on load
- Produces TypeScript definitions

**Build Configuration:**
- `rollup.config.js` - UMD build + TypeScript definitions
- Custom plugins: `fix()` adds header and auto-init, `spaces2tabs()` enforces indentation

## Testing Strategy

### PHP Tests (Nette Tester)

- **Location:** `tests/` directory
- **Format:** `.phpt` files with `test()` or `testException()` functions
- **Bootstrap:** `tests/bootstrap.php` sets up environment
- **Coverage:** ~100 test files covering all components

**Test Organization:**
- `tests/Forms/` - Core form tests (Controls, validation, rendering)
- `tests/Forms.DI/` - DI integration tests
- `tests/Forms.Latte/` - Latte template integration tests

**Common Test Patterns:**
```php
test('description of what is tested', function () {
	// test code
	Assert::same($expected, $actual);
});

testException('description', function () {
	// code that should throw
}, ExceptionClass::class, 'message pattern %a%');
```

### JavaScript Tests (Vitest)

- **Location:** `tests/netteForms/`
- **Files:** `Nette.validateRule.spec.js`, `Nette.validators.spec.js`
- **Setup:** `tests/netteForms/setup.js`
- **Environment:** jsdom for DOM testing

## Code Standards

### PHP Conventions

- Every file must have `declare(strict_types=1)`
- Use TABS for indentation (not spaces)
- All properties, parameters, and return values must have types
- Single quotes for strings (unless containing apostrophes)
- PascalCase for classes, camelCase for methods/properties
- No abbreviations unless full name is too long

### Recent Breaking Changes (v3.3)

- Latte Runtime refactored from static to non-static class
- Removed Latte 2 support (requires Latte 3.1+)
- Removed deprecated functionality
- Removed old class name compatibility

## Key Configuration Files

- `composer.json` - PHP dependencies, scripts
- `package.json` - JavaScript dependencies, build scripts
- `phpstan.neon` - Static analysis (level 5, Nette extension)
- `eslint.config.js` - TypeScript linting with @nette/eslint-plugin
- `rollup.config.js` - JavaScript build configuration
- `vitest.config.ts` - JavaScript test runner
- `tests/bootstrap.php` - Test environment setup

## Development Workflow

1. **PHP Changes:**
   - Modify source in `src/Forms/` or `src/Bridges/`
   - Run tests: `vendor/bin/tester tests -s`
   - Run PHPStan: `composer run phpstan`

2. **JavaScript Changes:**
   - Modify source in `src/assets/*.ts`
   - Build: `npm run build` (auto-runs tests after build)
   - Lint: `npm run lint:fix`

3. **Adding New Form Control:**
   - Create class in `src/Forms/Controls/`
   - Extend `BaseControl` or implement `Control` interface
   - Add validation support in `Validator.php` if needed
   - Add client-side validation in `src/assets/validators.ts`
   - Add tests in `tests/Forms/Controls.{ControlName}.*.phpt`

4. **Latte Integration Changes:**
   - Modify `src/Bridges/FormsLatte/`
   - Update Runtime or add/modify Nodes
   - Test in `tests/Forms.Latte/`

## Latte Template Integration

Nette Forms provides deep integration with Latte templating engine through custom tags and attributes.

### Core Latte Tags

**`{form}` and `{control}`:**
```latte
{* Simple rendering - outputs entire form *}
{control signInForm}

{* Manual form structure with {form} tag *}
{form signInForm}
	{* form content *}
{/form}
```

**`n:name` attribute** - Links PHP form definition with HTML:
```latte
<form n:name=signInForm class=form>
	<label n:name=username>Username: <input n:name=username size=20></label>
	<span class=error n:ifcontent>{inputError username}</span>
	<input n:name=send class="btn">
</form>
```

**`{input}` and `{label}` tags** - Universal rendering:
```latte
{label username}Username: {input username, size: 20, autofocus: true}{/label}
{inputError username}
```

**`{inputError}`** - Displays validation errors:
```latte
<span class=error n:ifcontent>{inputError $input}</span>
```

**`{formContainer}`** - Renders nested containers:
```latte
{formContainer emailNews}
	<ul>
		<li>{input sport} {label sport /}</li>
		<li>{input science} {label science /}</li>
	</ul>
{/formContainer}
```

### Rendering Patterns

**Automatic rendering** - Generic template for any form:
```latte
<form n:name=$form class=form>
	<ul class="errors" n:ifcontent>
		<li n:foreach="$form->getOwnErrors() as $error">{$error}</li>
	</ul>

	<div n:foreach="$form->getControls() as $input"
		n:if="$input->getOption(type) !== hidden">
		{label $input /}
		{input $input}
		{inputError $input}
	</div>
</form>
```

**RadioList/CheckboxList item-by-item:**
```latte
{foreach $form[gender]->getItems() as $key => $label}
	<label n:name="gender:$key"><input n:name="gender:$key"> {$label}</label>
{/foreach}
```

## Validation System

### Built-in Validation Rules

All rules are constants of `Nette\Forms\Form` class:

**Universal rules:**
- `Required` / `Filled` - required control
- `Blank` - control must be empty
- `Equal` / `NotEqual` - value comparison
- `IsIn` / `IsNotIn` - value in/not in array
- `Valid` - control filled correctly (for conditions)

**Text input rules:**
- `MinLength` / `MaxLength` / `Length` - text length validation
- `Email` - valid email address
- `URL` - absolute URL (auto-completes scheme)
- `Pattern` / `PatternInsensitive` - regex matching
- `Integer` / `Numeric` / `Float` - numeric validation
- `Min` / `Max` / `Range` - numeric range

**File upload rules:**
- `MaxFileSize` - maximum file size in bytes
- `MimeType` - MIME type validation (wildcards: `'video/*'`)
- `Image` - JPEG, PNG, GIF, WebP, AVIF validation

**Multiple items rules (CheckboxList, MultiSelect, MultiUpload):**
- `MinLength` / `MaxLength` / `Length` - count validation

### Error Message Placeholders

```php
$form->addInteger('id')
	->addRule($form::Range, 'at least %d and at most %d', [5, 10]);
	// %d - replaced by arguments
	// %n$d - replaced by n-th argument
	// %label - control label
	// %name - control name
	// %value - user input
```

### Custom Validators

**PHP side:**
```php
class MyValidators
{
	public static function validateDivisibility(BaseControl $input, $arg): bool
	{
		return $input->getValue() % $arg === 0;
	}
}

$form->addInteger('num')
	->addRule([MyValidators::class, 'validateDivisibility'],
		'Value must be multiple of %d', 8);
```

**JavaScript side** - Add to `Nette.validators`:
```js
Nette.validators['AppMyValidators_validateDivisibility'] = (elem, args, val) => {
	return val % args === 0;
};
```

### Validation Conditions

**Conditional validation:**
```php
$form->addPassword('password')
	->addCondition($form::MaxLength, 8)
		->addRule($form::Pattern, 'Must contain digit', '.*[0-9].*');
```

**Conditional on another control:**
```php
$form->addCheckbox('newsletters');
$form->addEmail('email')
	->addConditionOn($form['newsletters'], $form::Equal, true)
		->setRequired('Enter email');
```

**Complex structures:**
```php
$form->addText('field')
	->addCondition(/* ... */)
		->addConditionOn(/* ... */)
			->addRule(/* ... */)
		->elseCondition()
			->addRule(/* ... */)
		->endCondition()
		->addRule(/* ... */);
```

### Dynamic JavaScript (Toggle)

Show/hide elements based on conditions:
```php
$form->addCheckbox('send_it')
	->addCondition($form::Equal, true)
		->toggle('#address-container'); // Shows element when checked
```

Custom toggle behavior:
```js
Nette.toggle = (selector, visible, srcElement, event) => {
	document.querySelectorAll(selector).forEach((el) => {
		// Custom show/hide logic with animations
	});
};
```

## Form Configuration (NEON)

Customize default error messages:
```neon
forms:
	messages:
		Equal: 'Please enter %s.'
		Filled: 'This field is required.'
		MinLength: 'Please enter at least %d characters.'
		Email: 'Please enter a valid email address.'
		# ... other messages
```

Standalone usage (without framework):
```php
Nette\Forms\Validator::$messages['Equal'] = 'Custom message';
```

## Common Patterns

### Data Mapping to Classes

**Basic mapping:**
```php
class RegistrationFormData
{
	public string $name;
	public int $age;
	public string $password;
}

$data = $form->getValues(RegistrationFormData::class);
// Returns typed object instead of ArrayHash
```

**Nested containers:**
```php
class PersonFormData
{
	public string $firstName;
	public string $lastName;
}

class RegistrationFormData
{
	public PersonFormData $person;
	public int $age;
}

$person = $form->addContainer('person');
$person->addText('firstName');
$person->addText('lastName');

$data = $form->getValues(RegistrationFormData::class);
```

**Generate data class:**
```php
// Outputs class definition to browser
Nette\Forms\Blueprint::dataClass($form);
```

### Multiple Submit Buttons

```php
$form->addSubmit('save', 'Save');
$form->addSubmit('delete', 'Delete');

if ($form->isSuccess()) {
	if ($form['save']->isSubmittedBy()) {
		// Save logic
	}
	if ($form['delete']->isSubmittedBy()) {
		// Delete logic
	}
}
```

**Partial validation:**
```php
$form->addSubmit('preview')
	->setValidationScope([]); // No validation

$form->addSubmit('save')
	->setValidationScope([$form['name']]); // Only name field
```

### Containers for Grouped Controls

```php
$form->addContainer('personal')
	->addText('name')
	->addInteger('age');

$form->addContainer('address')
	->addText('street')
	->addText('city');

// Returns nested structure:
// ['personal' => ['name' => ..., 'age' => ...], 'address' => [...]]
```

### Control Value Filtering

```php
$form->addText('zip')
	->addFilter(fn($value) => str_replace(' ', '', $value))
	->addRule($form::Pattern, 'Must be 5 digits', '\d{5}');
```

### Omitted Values

Exclude values from `getValues()` result:
```php
$form->addPassword('passwordVerify')
	->addRule($form::Equal, 'Passwords do not match', $form['password'])
	->setOmitted(); // Not included in getValues()
```

## Security

### CSRF Protection

**Sec-Fetch/Origin header protection** (enabled by default):
```php
// Create form before sending output to set _nss cookie
$form = new Form;
```

**Cross-origin forms** (use carefully):
```php
$form->allowCrossOrigin(); // Disables CSRF protection!
```

### Automatic Security Features

- UTF-8 validation on all inputs
- Control character filtering
- Line break removal in single-line inputs
- Line break normalization in multi-line inputs
- Select/radio/checkbox forgery prevention
- Automatic whitespace trimming

### Safe Hidden Fields

```php
$form->addHidden('userId');
// WARNING: Hidden field values can be spoofed!
// Always validate on server side
```

## JavaScript Integration

### Loading netteForms.js

**Via CDN:**
```latte
<script src="https://unpkg.com/nette-forms@3"></script>
```

**Via npm:**
```bash
npm install nette-forms
```
```js
import netteForms from 'nette-forms';
netteForms.initOnLoad();
```

**Local copy:**
```latte
<script src="/path/to/netteForms.min.js"></script>
```

### Validation Transfer

Validation rules and conditions are automatically transferred to JavaScript via `data-nette-rules` HTML attributes. The script intercepts form submit and performs client-side validation.

### Disable Auto-init

```html
<script>
window.Nette = {noInit: true}; // Set before loading netteForms.js
</script>
<script src="netteForms.js"></script>
```

## Rendering Customization

### DefaultFormRenderer Configuration

Change wrapper elements via `$wrappers` array:
```php
$renderer = $form->getRenderer();
$renderer->wrappers['controls']['container'] = 'dl';
$renderer->wrappers['pair']['container'] = null;
$renderer->wrappers['label']['container'] = 'dt';
$renderer->wrappers['control']['container'] = 'dd';
```

### Control Groups (Fieldsets)

```php
$form->addGroup('Personal data');
$form->addText('name');
$form->addInteger('age');

$form->addGroup('Shipping address');
$form->addText('street');
$form->addText('city');
```

### HTML Attributes

**Per-item attributes (RadioList, CheckboxList):**
```php
$form->addCheckboxList('colors', 'Colors:', ['r' => 'red', 'g' => 'green'])
	->setHtmlAttribute('style:', ['r' => 'background:red', 'g' => 'background:green']);
	// Colon after 'style:' selects value by key
```

**Boolean attributes:**
```php
$form->addCheckboxList('colors', 'Colors:', $colors)
	->setHtmlAttribute('readonly?', 'r'); // Only 'r' gets readonly
```

**Select option attributes:**
```php
$form->addSelect('colors', 'Colors:', $colors)
	->setOptionAttribute('style:', $styles);
```

### Control Prototypes

Modify HTML templates directly:
```php
$input = $form->addInteger('number');
$input->getControlPrototype()->class('big-number');
$input->getLabelPrototype()->class('distinctive');

// Container wrapper (Checkbox, CheckboxList, RadioList)
$input->getContainerPrototype()->setName('div')->class('check');
```
