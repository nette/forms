# To My Agents!

It is my fervent wish that this file guide every AI coding agent working with code in this repository.

## Documentation

Any distilled, agent-facing documentation for this package - how it works
internally and the rationale behind key design decisions - lives in `docs/`.
Consult it before non-trivial changes; it is the source of truth from which the
public manual is distilled.

Submission detection, the data flow, validation evaluation, control value
semantics, rendering, the repeater, and the client-side engine each carry sharp
traps (the write-once submission source, accumulating filters, message-override
keying, the export stop at a filter, silently skipped client validators). Read
`docs/internals/` before touching them; it describes the code on the current
branch - nothing more, nothing less.

`docs/upgrading.md` records, per released version, every change visible to
users of the package (renames, removals, deprecations, signatures, behavior,
configuration), each with its commit hash; internal changes are not in it. It
is the place to look up the history of a public symbol. Do not write to it; it
is extended at release time from the git history.

## Project Overview

Nette Forms (since 2004) creates, validates, and processes web forms with **both**
server-side (PHP) and client-side (TypeScript in `src/assets/`, the npm package
`nette-forms`) validation kept in sync.

- **PHP Version**: 8.4 - 8.6
- **Package**: `nette/forms`
- **Dependencies**: nette/component-model 4, nette/http 3.4+, nette/utils 4.1+;
  optional Latte 3.1 (3.1.5+), nette/application 3.3.2+, nette/di; ext-intl for
  date/time controls.

## Essential Commands

```bash
# PHP tests / static analysis
vendor/bin/tester tests -s -C        # or: composer tester
vendor/bin/tester tests/Forms/ -s -C
composer phpstan                     # PHPStan level 8

# JavaScript (client-side validator) - uses npm
npm install
npm run build       # UMD + ESM (.mjs) + minified + .d.ts into src/assets/dist/ (not in git)
npm run test        # builds first (pretest), then Vitest (jsdom); test:watch / test:ui also available
npm run lint:fix    # ESLint with @nette/eslint-plugin
npm run typecheck
```

## Conventions

- Every PHP file starts with `declare(strict_types=1);`; **tabs** for indentation;
  everything typed; single quotes unless the string has an apostrophe; Nette Coding
  Standard. JS source is TypeScript in `src/assets/`, built by Rollup (a
  `spaces2tabs()` plugin enforces tabs, `fix()` adds the header + auto-init).
- PHP tests are Nette Tester `.phpt` (`tests/Forms/`, `tests/Controls/`,
  `tests/Forms.DI/`, `tests/Forms.Latte/`, `tests/types/`); JS tests are Vitest
  specs in `tests/netteForms/`, run against the built files. Their repeater
  fixtures are rendered by PHP: regenerate them with
  `php tests/netteForms/fixtures/generate.php` after changing repeater markup.

## Working in this repo

- **Validation is dual-sided: a rule lives in BOTH places.** A server rule is a
  validator object in `src/Forms/Validation/` (string operations such as
  `Form::Email` map to these objects in `Validation\Operations`); its
  `getClientRule()` names the client twin in `src/assets/validators.ts`, exported
  via `data-nette-rules`, and declares the HTML attributes it implies. Adding or
  changing a rule means editing PHP *and* TypeScript, then `npm run build`.
- **Filters accumulate.** A filter changes the value in place and re-applies on
  every validation pass, including each `Form::Valid` condition that references
  the control. Toggle computation is a dry run and changes nothing. A filter
  without a client twin also stops the client export and the HTML attributes of
  all rules after it. See `docs/internals/validation.md`.
- **Message overrides** (`Validator::$messages`, NEON `forms: messages:`) are keyed
  by message id (`forms.length.min`) or by operation, and reach every validator
  object; a plain callable rule never picks one up.
- **The submission source is write-once and must be set before anything reads
  the form data**; adding a control already materializes the default
  `HttpSource`. See `docs/internals/submission.md`.
- **CSRF protection is the same-origin `Sec-Fetch-Site` check of `HttpSource`**;
  there is no token (`addProtection()` is a no-op). `allowCrossOrigin()` disables
  protection entirely - use with care.
- **A new control** extends `BaseControl` (`src/Forms/Controls/`) or implements the
  whole `Control` interface; it picks the data type in `loadHttpData()`, lists the
  rule-declared HTML attributes it renders in `getRuleAttributeNames()`, and gets
  tests on both sides when it has client behaviour.
- User-facing how-to (Latte tags, validation-rule catalog, conditions/toggles, NEON
  messages, data mapping, rendering customization, JS loading) is manual material
  and lives in the public web docs, not here.
