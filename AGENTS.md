# To My Agents!

It is my fervent wish that this file guide every AI coding agent working with code in this repository.

## Documentation

Any distilled, agent-facing documentation for this package - how it works
internally and the rationale behind key design decisions - lives in `docs/`.
Consult it before non-trivial changes; it is the source of truth from which the
public manual is distilled.

The data flow, validation evaluation, control value semantics, rendering, and
the client-side engine each carry sharp traps (the `:valid` re-validation loop,
accumulating filters, message-override keying, silently skipped client
validators). Read `docs/internals/` before touching them; it describes the code
on the current branch - nothing more, nothing less.

## Project Overview

Nette Forms (since 2004) creates, validates, and processes web forms with **both**
server-side (PHP) and client-side (`netteForms.ts`) validation kept in sync.

- **PHP Version**: 8.3 - 8.5
- **Package**: `nette/forms`
- **Dependencies**: nette/component-model, nette/http, nette/utils; Latte 3.1.4+.

## Essential Commands

```bash
# PHP tests / static analysis
vendor/bin/tester tests -s -C        # or: composer tester
vendor/bin/tester tests/Forms/ -s -C
composer phpstan                     # PHPStan level 8

# JavaScript (client-side validator) - uses npm
npm install
npm run build       # UMD + minified + .d.ts into src/assets/; runs JS tests after
npm run test        # Vitest (jsdom); test:watch / test:ui also available
npm run lint:fix    # ESLint with @nette/eslint-plugin
npm run typecheck
```

## Conventions

- Every PHP file starts with `declare(strict_types=1);`; **tabs** for indentation;
  everything typed; single quotes unless the string has an apostrophe; Nette Coding
  Standard. JS source is TypeScript in `src/assets/`, built by Rollup (a
  `spaces2tabs()` plugin enforces tabs, `fix()` adds the header + auto-init).
- PHP tests are Nette Tester `.phpt` (`tests/Forms/`, `tests/Forms.DI/`,
  `tests/Forms.Latte/`); JS tests are Vitest specs in `tests/netteForms/`.

## Working in this repo

- **Validation is dual-sided: a rule lives in BOTH places.** A server rule is a
  `Validator::validateXxx` method; its client twin goes in `src/assets/validators.ts`
  and is exported via `data-nette-rules`. Adding/changing a rule means editing PHP
  *and* TypeScript, then `npm run build`.
- **`:valid` toggle evaluation is a known trap.** Computing toggle states runs full
  validation (with filters and `addError`) and there is **no recursion guard**, so
  it can mutate values and add phantom errors. Filters also **accumulate** (each pass
  re-applies to the already-filtered value). See `docs/internals/validation.md`.
- **Custom-message override is keyed by the operation string** and only reaches
  string validators - a callable/object validator never picks it up.
- **CSRF defaults to same-origin `Sec-Fetch-Site` checking** (token-based protection
  is deprecated); `allowCrossOrigin()` disables protection entirely - use with care.
- **A new control needs a PHP class** (`src/Forms/Controls/`, extend `BaseControl`),
  optional `Validator.php` support, a client validator, and tests on both sides.
- User-facing how-to (Latte tags, validation-rule catalog, conditions/toggles, NEON
  messages, data mapping, rendering customization, JS loading) is manual material
  and lives in the public web docs, not here.
