# Client-side validation

The TypeScript sources in `src/assets/` re-implement the server's rule evaluation
in the browser. `formValidator.ts` is the engine (`FormValidator`: evaluation,
toggles, form wiring), `validators.ts` holds the rule twins (`Validators`, one
method per op), `repeater.ts` the repeater support (`docs/internals/repeater.md`).
`nette.ts` composes them into `NetteForms` (adding `version` from
`dist/package.json` and the `repeater`) and exports the one shared instance.

## Two builds of one instance

Rollup builds into `src/assets/dist/`, which is not in git except its
`package.json` (the npm package manifest):

- **UMD** (`index.umd.ts`): the global `Nette`. The `fix()` plugin rewrites the
  banner so the file **calls `initOnLoad()` by itself**, unless the page pre-sets
  `Nette = {noInit: true}`.
- **ES module** (`index.esm.ts`): the default export is the same kind of instance,
  **not initialized**; the importer calls `initOnLoad()`. It also exports the
  `FormValidator` and `Repeater` classes and the types.

`NetteForms::initOnLoad()` wires the forms and installs the repeater's delegated
click listener. The Vitest specs run the built files, hence `pretest` builds.

The engine mirrors `Rules::validate()`: `emptyOptional` computed via `:filled`, a
`:blank` condition resetting it in the branch, `~` negation parsed from the op
string, conditions recursing. The invariants around it differ from PHP in ways an
agent must know.

## Op resolution: unknown validators are skipped silently

`validateRule()` maps `':minLength'` → `validators.minLength`; an exported static
callable `Custom::method` becomes method name `Custom_method` (`::` → `_`,
backslashes stripped). **A missing method returns `null` and the rule is simply
skipped**, the opposite of PHP, where an unknown op throws in `addRule()`. A server
rule exported without a JS twin therefore validates as if it did not exist, with
no error anywhere. Validators may themselves return `null` ("cannot decide", e.g.
a malformed pattern) with the same effect, and so does a rule whose argument
references a control that is not rendered.

## Values: filters never touch the DOM

`getValue()` normalizes per element type (radio/checkbox-list expansion via
`RadioNodeList`, `FileList` for uploads, trimmed strings for text inputs).
`getEffectiveValue()` additionally maps `data-nette-empty-value` to `''` and can
apply filters, but filtering runs the rules with a `{value}` **ref object**;
normalizing validators (`url`, `integer`, `float`) write into that ref, never into
the input. Combined with the `#preventFiltering` re-entrancy guard, **filters do
not accumulate and validation does not mutate visible state**. Messages get
`%value` and the `%N` control references substituted from the current values.

## Form wiring (`initForm`)

Forms without any `data-nette-rules` element are left alone, unless they contain
a repeater (whose validated fields may exist only in its `<template>`). Otherwise
the engine computes initial toggles and, unless the form already had `novalidate`,
sets `form.noValidate = true` (taking over from HTML5) and installs a `submit`
handler that cancels submission on failure. `validateForm()` first fails on any
`badInput` element (letting the browser report it via `reportValidity`, except in
`onlyCheck` mode), then walks `form.elements` with rules; a sender with
`formnovalidate` narrows both passes via a **regex built from
`data-nette-validation-scope`** matched against the rewritten control name
(`a[b][c]` → `a-b-c-`). Errors accumulate in `formErrors` and are shown at the end
in a `<dialog>` modal (fallback `alert`), focusing the first offender. `reset`
re-runs toggles; a GET form with `data-nette-compact` sends checkbox-list values
as one comma-joined field via the `formdata` event.

`elem.validity.badInput` also short-circuits `validateRule` itself: such an
element counts as *filled* but nothing else.

## Toggles

`toggleForm()` recomputes **all** toggles from scratch, OR-combining states per
id, then applies them via `toggle()`, which treats a `^\w[\w.:-]*$` selector as an
id, otherwise as a CSS selector, and flips `hidden`. A pass without an event
attaches a `change` listener (deduplicated through a `WeakMap`) to each control
participating in a condition, so elements added later (repeater items) get theirs
on the next such pass. `toggle()` receives the source element and event precisely
so userland can override it (animations etc.).

## `:submitted` depends on an external writer

`Validators.submitted` compares `elem.form['nette-submittedBy'] === elem`, but
**nothing in this package ever assigns that property**; integrations (e.g. Naja)
set it on button click. Without one, a client-side `:submitted` condition is always
false.
