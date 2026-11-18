# Validation

The densest part of the package, and the one with the sharpest traps.

## Validator objects and string operations

A validator is an object in `src/Forms/Validation/`. `Validator` is the common
interface (`getClientRule()`, `null` for server-only); a usable one also
implements `ValueValidator` (`validate($value)`), `ControlValidator`
(`validateControl($control)`) or `Filter` (`filter($value)`), otherwise `addRule()`
throws. Failure is reported as a `Message` (stable id, default English template,
parameters, optional `count`), success as `null`.

String operations (`Form::Email`, `':minLength'`…) are a front end:
`Validation\Operations::toValidator()` maps each to a validator object, choosing by
the control (the length operations become `ItemCountValidator` on multi-value
controls, `LengthValidator` otherwise). **Only `:static`, `:submitted` and `:valid`
have no object**; the first two still run through `Validator::validateStatic()` /
`validateSubmitted()`, `:valid` is handled inside `Rules`. A plain callable is
called as `($control, $arg)` and reports only a bool. Adding a built-in rule means
a validator class, its `Operations` entries (`toValidator`, `assertArgument`, the
message-id map) and a client twin in `src/assets/validators.ts`.

## The rule tree

`Rules` holds an ordered `$rules[]`, a **separate** `$required` slot and a
`$parent`. A `Rule` is an **immutable** value object: `control`, `validator`
(string op, validator object, `Filter` or callable), `arg`, `message`,
`isNegative`, and `branch` (a nested `Rules`) for conditions;
`Rule::getValidationObject()` memoizes the object a string op maps to.

- `addRule` checks the argument **eagerly**: a string op's `$arg` must match its
  type (`Operations::assertArgument()`), a validator object takes no `$arg`. A rule
  that checks filled-ness (`Rule::checksFilled()`, i.e. a `FilledValidator`) goes
  to `$required`; any other rule with an identical validator and argument
  (`===`) **replaces** the existing one in place, so a custom message overrides the
  built-in rule of `addEmail()` etc.
- `Form::Valid` is rejected by `addRule` and `addCondition`; only `addConditionOn`
  accepts it. Negated (`~`) ops throw in `addRule`; negation exists only in
  conditions and `elseCondition`, which flips `Filled`↔`Blank` for string ops and
  `isNegative` otherwise.
- `addCondition` turns a bool into `:static`; `addConditionOn` returns the branch,
  which is why `addRule` chains into the condition.
- `removeRule` never removes conditions. A validator class removes all rules
  performed by it; a string op removes rules declared with it or, when its class
  is not shared with other ops, with that class; an object or callable removes
  only rules holding that very instance.
- `addFilter` wraps the callable in a `CallbackFilter` rule.

## Evaluation

`Rules::evaluateRule()` is the **single evaluation point** for all validator kinds.
`ControlValidator` wins over `ValueValidator` when an object is both (equality and
range validators use it to delegate to `DateTimeControl`). After a successful,
non-negated rule whose object is a `Filter`, the control value is replaced by
`filter(getValue())`: this is how `addFilter` works and how the built-in
`Integer`, `Float` and `URL` normalize the value.

`Rules::validate()`:

- **`$emptyOptional`** = "not required and not filled". When set, every
  **non-branch** rule except the filled check is skipped, so an empty optional field
  validates clean. It propagates into branches, but a branch on a blank check
  (`Rule::checksBlank()`, including a negated filled check) resets it to `false`.
- **Order is Blank → required → the rest**, imposed by `getIterator()`, not by
  insertion order. The first group takes only blank checks on the **same**
  control. Filters are ordinary rules in the last group, interleaved with the others.
- The first failing rule adds its message to the control and stops the control's
  validation.

## Dry run: toggles never mutate

`Rules::getToggleStates()` (behind `Form::getToggles()`) evaluates in a **dry run**:
no filter is applied and no error added, also through `:valid` conditions. A dry
run sees the unfiltered value, so its result can differ from real validation when
a filter precedes the checked rule.

## Trap: filters re-apply on every validation pass

A filter mutates the control value in place and **nothing restores the raw value**.
Every validating pass re-runs it on the already-filtered value:
`addFilter(fn($v) => $v . 'x')` appends another `x` per pass. Passes come from each
`validate()` call and from every `:valid` condition referencing the control: such
a condition evaluates the referenced control's rules **for real**, applying its
filters and adding errors to it, on top of that control's own validation (one
`$form->validate()` then applies the filter twice).

## `:valid` and its cycle guard

`Rules::evaluateReferenced()` runs the referenced control's full rule list and
flags it while running; re-entering the same `Rules` throws
`InvalidStateException` ("form a cycle"), in validation and toggles alike.
`Validator::validateValid()` bypasses this path and the guard.

## Messages

`MessageFormatter::format()` chooses the template in this order:

1. an `HtmlStringable` rule message is returned as is (no translation, no
   placeholders);
2. a string rule message is translated as a string, with the count (the failure's
   `count`, or an int `arg`) as the second translator argument;
3. otherwise the failure `Message` reported by the validator (or a `Message` given
   as the rule message): `Validator::$messages[$id]`, then
   `Validator::$messages[<operation of the id>]` (`Operations::getMessageOperation()`),
   then its default template. **The translator receives the `Message` object**,
   whose string form is the unsubstituted template, so a translator keyed by the
   English text matches it;
4. a callable (and `:static`, `:submitted`) reports no message: without a rule
   message it is a "Missing validation message" warning.

Overrides therefore reach validator objects by their message id and string ops by
the op; a plain callable never picks one up. Parameters are substituted **after**
translation: the named ones of the `Message`, then the positional `%d`/`%s` and
`%label`, `%value`, `%name`. The DI extension writes `forms: messages:` keys
containing a dot as ids, other keys as `Form` or global constant names.
Errors without a rule (POST size, repeater counts) go through `formatStandalone()`,
which applies the overrides and translation but leaves `%label`, `%value` and
`%name` untouched. The `maxlength` check of `TextBase` formats its message as a
`MaxLength` rule of the control, so all placeholders work there.

## DateTimeControl is special-cased across the pipeline

Distant sites test `instanceof DateTimeControl` and break together in a refactor:

- `Operations::toValidator()` converts string/int arguments of
  `Equal`/`NotEqual`/`Min`/`Max`/`Range` to `DateTimeImmutable`.
- `EqualValidator`/`NotEqualValidator::validateControl()` delegate to
  `DateTimeControl::validateEqual()`, `RangeValidator::validateControl()` to
  `validateMinMax()`. Both normalize to the control's type (a time compares by
  time of day, a date ignores the time), and a time range **wraps around
  midnight** when min > max.
- `MessageFormatter` shows dates via `formatLocaleText()` (`IntlDateFormatter`,
  needs ext-intl).
- `ClientRuleExporter` exports arguments via `formatHtmlValue()` (the `<input>`
  wire format), so the client compares strings in that format.

## Client export

`ClientRuleExporter::export()` serializes rules into `data-nette-rules`, each from
`Rule::getClientRule()`, or from the op/static-callable name (`Class::method`) and
the raw argument when there is no object. A rule exports only if
`Rule::canExport()`:

- A **mutating** rule (`Rule::mutatesValue()`: a `Filter` without a client rule,
  typically `addFilter`) **stops** the export of everything after it, including
  the rules after a condition whose branch contains one: the client cannot
  reproduce the server-side value. A filter with a client rule (`Integer`,
  `Float`, `URL`) is reproduced by its JS twin and does not stop it.
- A non-mutating server-only rule (a non-static callable, an object with no client
  rule, `SelectBox`'s closure condition) is skipped.
- A condition with neither exported rules nor toggles is dropped.

`Form::Enum` exports as `:equal` against the case values. Messages are exported
with `%value` and control references (`%0`, `%1`) left for the client.

**HTML validation attributes** (`maxlength`, `min`, `max`, `pattern`, `type`,
`inputmode`, `accept`) are declared by validators in `ClientRule::$attributes` and
collected by `Rules::getHtmlAttributes()` under the same stop rule; conditional
and negated rules never contribute, and duplicates merge to the stricter bound
(`Rules::mergeAttribute()`). The control renders only the names its
`getRuleAttributeNames()` returns (see `docs/internals/controls.md`), and a value
set on the prototype wins for non-bounds (`type`) or merges for bounds. So
`addText()->addFilter(…)->addRule(Form::Email)` renders `type="text"`.

How the browser consumes the payload is described in
`docs/internals/client-side.md`.
