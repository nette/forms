Nette Forms: greatly facilitates web forms
==========================================

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/forms.svg)](https://packagist.org/packages/nette/forms)
[![Build Status](https://travis-ci.org/nette/forms.svg?branch=master)](https://travis-ci.org/nette/forms)
[![Coverage Status](https://coveralls.io/repos/github/nette/forms/badge.svg?branch=master)](https://coveralls.io/github/nette/forms?branch=master)
[![Latest Stable Version](https://poser.pugx.org/nette/forms/v/stable)](https://github.com/nette/forms/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/forms/blob/master/license.md)

Nette\Forms greatly facilitates creating and processing web forms. What it can really do?

- validate sent data both client-side (JavaScript) and server-side
- provide high level of security
- multiple render modes
- translations, i18n

Why should you bother setting up framework for a simple web form? You won't have to take care about routine tasks such as writing two validation scripts (client and server) and your code will be safe against security breaches.

Nette Framework puts a great effort to be safe and since forms are the most common user input, Nette forms are as good as impenetrable. All is maintained dynamically and transparently, nothing has to be set manually. Well-known vulnerabilities such as Cross Site Scripting (XSS) and Cross-Site Request Forgery (CSRF) are filtered, as well as special control characters. All inputs are checked for UTF-8 validity. Every multiple-choice, select boxes and similar are checked for forged values upon validating. Sounds good? Let's try it out.


Documentation
-------------

This is just a piece of documentation. [Please see our website](https://doc.nette.org/forms).


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

Although we mentioned validation, our form has none. Let's fix it. In order to require user's name, call `setRequired()` method on the form item. You can pass an error message as optional argument and it will be displayed if user does not fill his name in:


```php
$form->addText('name', 'Name:')
	->setRequired('Please fill your name.');
```

Try submitting a form without the name - the message is displayed unless you meet the validation rules.

The form is validated on both the client and server side. You only need to link `netteForms.js`, which is located at `/src/assets` in the distribution package.

```html
<script src="netteForms.js"></script>
```

Nette Framework adds `required` class to all mandatory elements. Adding the following style will turn label of *name* input to red.

```html
<style>
.required label { color: maroon }
</style>
```

[Continue…](https://doc.nette.org/forms).
