Nette Forms: greatly facilitates web forms
==========================================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/forms.svg)](https://packagist.org/packages/nette/forms)
[![Build Status](https://travis-ci.org/nette/forms.svg?branch=master)](https://travis-ci.org/nette/forms)

Nette\Forms greatly facilitates creating and processing web forms. What it can really do?

- validate sent data both client-side (JavaScript) and server-side
- provide high level of security
- multiple render modes
- translations, i18n

Why should you bother setting up framework for a simple web form? You won't have to take care about routine tasks such as writing two validation scripts (client and server) and your code will be safe against security breaches.

Nette Framework puts a great effort to be safe and since forms are the most common user input, Nette forms are as good as impenetrable. All is maintained dynamically and transparently, nothing has to be set manually. Well known vulnerabilities such as Cross Site Scripting (XSS) and Cross-Site Request Forgery (CSRF) are filtered, as well as special control characters. All inputs are checked for UTF-8 validity. Every multiple-choice, select boxe and similar are checked for forged values upon validating. Sounds good? Let's try it out.

[See documentation](http://doc.nette.org/en/forms).

First form
----------

Let's create a simple registration form:

```php
use Nette\Forms\Form;

$form = new Form;

$form->addText('name', 'Name:');
$form->addPassword('password', 'Password:');
$form->addSubmit('send', 'Register');

echo $form; // renders the form
```
Though we mentioned validation, yet our form has none. Let's fix it. We require users to tell us their name, so we should call a `setRequired()` method, which optional argument is an error message to show, if user does not fill his name in:

```php
$form->addText('name', 'Name:')
	->setRequired('Please fill your name.');
```

Try submitting a form without the name - you will keep seeing this message until you meet the validation rules. All that is left for us is setting up JavaScript rules. Luckily it's a piece of cake. We only have to link `netteForms.js`, which is located at `/client-side/forms` in the distribution package.

```html
<script src="netteForms.js"></script>
```

Nette Framework adds `required` class to all mandatory elements. Adding the following style will turn label of *name* input to red.

```html
<style>
.required label { color: maroon }
</style>
```

[Continue…](http://doc.nette.org/en/forms).
