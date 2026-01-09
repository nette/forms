# Validation (v3.x)

The densest part of the package, and the one with the sharpest traps.

## The rule tree

`Rules` holds an ordered `$rules[]`, a **separate** `$required` slot, and a
`$parent` pointer. A `Rule` is a **mutable** value object: `control`, `validator`
(**either a string op like `':email'` or a callable**), `arg`, `isNegative`,
`message`, and an optional `branch` (a nested `Rules` for conditions). There is no
`op` field — the op string *is* the validator, mapped to a built-in via
`getCallback()` (`':email'` → `[Validator::class, 'validateEmail']`).

- `addRule`: a `Filled` rule goes to `$required`, everything else to `$rules[]`;
  `Valid` may not be a rule.
- `addCondition` turns a bool argument into `:static`; `addConditionOn` creates a
  `Rule` with a `branch` and **returns the branch**, which is why `addRule` chains
  into the condition.
- `elseCondition` clones the last rule and flips it — `Filled`↔`Blank` via a lookup,
  otherwise `isNegative = !isNegative`.

## Evaluation order and `$emptyOptional`

`Rules::validate()`:

- **`$emptyOptional`** = "not required and not filled". When set, every **non-branch**
  rule except `Filled` is skipped — an empty optional field validates clean. It
  propagates into branches, but a `Blank` branch resets it to `false`.
- **Order is Blank → required → the rest**, imposed by `getIterator()` (priorities
  0/1/2), not by insertion order.
- **Filters are ordinary rules.** `addFilter` appends a rule whose closure does
  `setValue(filter(getValue())); return true;`, at priority 2 — so a filter runs
  **after** Blank and Required, interleaved with other rules, not in a dedicated
  phase.

## Trap: filters re-apply on every validation pass

A filter mutates the control's value in place and **nothing ever restores the raw
value**. Every call to `validate()` — direct, via `:valid`, or via toggle
computation — re-runs the filter on the **already-filtered** value. A
`addFilter(fn($v) => $v.'x')` appends another `x` each pass. In particular,
computing toggle states calls the same `validateRule()` with full side effects, so
**every render that reads toggles mutates values and can add phantom errors**
(there is no dry-run mode in v3.x; `validateRule` always executes the callback).

## Trap: `:valid` runs full validation and has no recursion guard

The `:valid` op routes to `Validator::validateValid()`, which calls
`$control->getRules()->validate()` — the **full** pipeline, applying filters and
calling `addError`. Because toggle computation (`getToggleStates`) goes through the
same `validateRule` path, a condition on `:valid` writes errors and mutates values
on every toggle read. Worse, there is **no visited-set**: `A` conditioned on
`B :valid` and `B` conditioned on `A :valid` recurse until the stack overflows.

## Trap: `Validator::$messages` is keyed by op, so it misses callable validators

`Validator::formatMessage()` resolves the message template from `$rule->validator`
**only when it is a string** (`is_string($rule->validator) && isset($messages[...])`).
So `Validator::$messages[Form::Email]` (and the NEON `forms: messages:`) overrides
`addRule(Form::Email)` but **never** a custom callable/object rule — those fall back
to the rule's explicit message or a "Missing validation message" error. There is no
stable message id in v3.x; the op string is the only key.

## Client export

`Helpers::exportRules()` serializes rules into `data-nette-rules`. A rule exports
only if `canExport()` — `is_string($validator) || Callback::isStatic($validator)`.
A non-exportable **non-branch** rule (typically a mutating filter closure) **`break`s**
and stops export of everything after it; inside a branch it merely `continue`s.
`Form::Enum` has no JS counterpart and is exported as `Form::Equal` against the
enum's case values. This "a mutating filter halts client validation of later rules"
behavior is intentional: the client cannot reproduce an opaque server-side mutation.
