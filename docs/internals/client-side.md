# Client-side validation

The TypeScript sources in `src/assets/` re-implement the server's rule evaluation
in the browser. `formValidator.ts` is the engine (`FormValidator` class:
evaluation, toggles, form wiring), `validators.ts` holds the rule twins
(`Validators` class, one method per op), `index.umd.ts` glues them into the
exported `Nette` object (plus `version` and `webalize`). The Rollup build's
`fix()` plugin rewrites the UMD banner so the built file **auto-runs
`initOnLoad()`** unless the page pre-sets `Nette = {noInit: true}`.

The engine mirrors `Rules::validate()` semantics — `emptyOptional` computed via
`:filled`, a `:blank` condition resetting it in the branch, `~` negation parsed
from the op string, conditions recursing — but the invariants around it differ
from PHP in ways an agent must know.

## Op resolution: unknown validators are skipped silently

`validateRule()` maps `':minLength'` → `Validators.minLength`; an exported static
callable `Custom::method` becomes method name `Custom_method` (`::` → `_`,
backslashes stripped). **A missing method returns `null` and the rule is simply
skipped** — the exact opposite of PHP, where an unknown string op throws in
`addRule()`. A server rule exported without a JS twin therefore validates as if
it did not exist, with no error anywhere. Validators may themselves return `null`
("cannot decide", e.g. malformed regexp) with the same skip effect.

## Values: filters never touch the DOM

`getValue()` normalizes per element type (radio/checkbox-list expansion via
`RadioNodeList`, `FileList` for uploads, trimmed strings for text inputs).
`getEffectiveValue()` additionally maps the `data-nette-empty-value` attribute
(exported by `TextBase`) to `''` and can apply filters — but filtering runs the
rules with a `{value}` **ref object**; normalizing validators (`url`, `integer`,
`float`) write into that ref, never into the input. Combined with the
`#preventFiltering` re-entrancy guard, the client is immune to both PHP traps:
**filters do not accumulate and validation does not mutate visible state**.

## Form wiring (`initForm`)

Forms without any `data-nette-rules` element are left alone. Otherwise the
engine computes initial toggles and — unless the form already had `novalidate` —
sets `form.noValidate = true` (taking over from HTML5) and installs a `submit`
handler that cancels submission on failure. `validateForm()` first lets the
browser report any `badInput` element (`reportValidity`), then walks elements
with rules; a sender with `formnovalidate` narrows validation via a **regex
built from `data-nette-validation-scope`** matched against the rewritten control
name (`a[b][c]` → `a-b-c-`). Errors accumulate in `formErrors` and are shown at
the end in a `<dialog>` modal (fallback `alert`), focusing the first offender.
`reset` re-runs toggles; a GET form with `data-nette-compact` compacts
checkbox-list values into one comma-joined field via the `formdata` event.

`elem.validity.badInput` also short-circuits `validateRule` itself: such an
element counts as *filled* but nothing else.

## Toggles

`toggleForm()` recomputes **all** toggles from scratch into `#formToggles`,
OR-combining states per id, then applies them via `toggle()` — which treats a
`^\w[\w.:-]*$` selector as an id, otherwise as a CSS selector, and flips
`hidden`. On the first pass each control participating in a condition gets a
`change` listener (deduplicated through a `WeakMap`) that re-runs `toggleForm`.
`toggle()` receives the source element and event precisely so userland can
override it (animations etc.).

## `:submitted` depends on an external writer

`Validators.submitted` compares `elem.form['nette-submittedBy'] === elem`, but
**nothing in this package ever assigns that property** — integrations (e.g.
Naja) set it on button click. Without one, a client-side `:submitted` condition
is always false.
