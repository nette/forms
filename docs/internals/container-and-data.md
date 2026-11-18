# Container tree & data flow

`Form extends Container extends Nette\ComponentModel\Container`. The tree and the
way submitted data reaches each control are one emergent model. How the form
learns that it was submitted is in `docs/internals/submission.md`.

## Data is pulled, per control, through the tree

There is **no central distribution** of submitted data. Each control pulls its own
value, lazily, driven by the component monitor:

- `BaseControl`'s constructor registers `monitor(Form::class, …)`; when a
  **non-disabled** control is attached to an **anchored, submitted** form it calls
  `loadHttpData()`, the template-method hook each control overrides to pick its
  data type. `setDisabled(false)` on a submitted form loads again.
- `BaseControl::getSubmittedValue($type, $key)` reads its parent's
  `Container::getSubmittedData()`, which reads its own parent's, up to
  `Form::getSubmittedData()`; each level returns the sub-array under its name (or
  `[]`). Only a **top-level control** looks its name up through
  `Helpers::sanitizeHtmlName()`, mirroring the `_` prefix `generateHtmlName()`
  gives a whole HTML name that is numeric or clashes with a form property; a
  container's name is never prefixed (`5[x]`).
- `Repeater::getSubmittedData()` renumbers its items to a list, so item
  containers `0`, `1`, … read the renumbered data. Anything that walks the raw
  form data by HTML name (the deprecated `Form::getHttpData()`,
  `BaseControl::getHttpData()`, `Helpers::extractHttpData()`) sees the original
  keys instead.
- **An explicitly set `name` attribute is an absolute path** into the raw form
  data, bypassing the tree (and any repeater renumbering); setting it on a
  submitted form reloads the value.

**Sanitization is by data-type bit** (`Helpers::sanitizeData`): `DataText`
normalizes newlines; `DataLine` collapses newlines to spaces and trims
(single-line inputs); `DataFile` passes only a `FileUpload`. `DataList` (renumber)
or `DataArray` (keep keys) on top expects an array, sanitizes each element with
the remaining bits and drops elements that became `null`; a non-array yields `[]`.
A control picking `DataText` for a single-line field leaks newlines.

## `fireEvents` order

`Form::fireEvents()` runs a fixed sequence: return if not submitted; validate only
if there are no errors yet; then `$submittedBy->onClick`/`onInvalidClick` (for a
`SubmitButton`), then `onSuccess` (if valid), then `onError` (if invalid), then
always `onSubmit`; a warning fires if nothing was handled.
`Container::resolveHandlerArguments()` (shared with `onValidate`) inspects the
types of each handler's first two parameters to pass the form/container, the
button or the values in whichever order they are declared; `onValidate` gets
untrusted values. `invokeHandlers` **stops the chain the moment a handler
invalidates the form**.

`Form::validate()` takes the validation scope from the submitting
`SubmitterControl`, runs `validateMaxPostSize()` (a form-level error when
`CONTENT_LENGTH` exceeds `post_max_size`), then `Container::validate($controls)`.
A disabled control skips validation entirely.

## Reading values back out of the tree

- **`getValues()` = `getUntrustedValues()` + guards.** On a submitted form it
  **throws** if called during validation, warns if the form is invalid, and narrows
  to the submitter's validation scope (a container is included when one of its
  ancestors is in scope).
- **`getUntrustedValues()`** walks the components: non-omitted `Control`s
  contribute `getValue()` (coerced by `Helpers::tryEnumConversion()` when the target
  member is a backed enum), nested containers recurse with the type of their target
  member. A repeater yields its items indexed from 0
  (`docs/internals/repeater.md`).
- **Mapping to a class** (`$returnType`, or `setMappedType()`) goes through
  `Helpers::mapToObject()`: a class whose constructor has **any** parameters is
  created through it with named arguments; otherwise the values fill public
  properties of a new instance or of the passed object. Errors are strict
  `InvalidStateException`s: a value with no parameter or no publicly writable
  property, or a required parameter without a value (because the control is
  missing, omitted or outside the scope). Dynamic properties are allowed only with
  `#[AllowDynamicProperties]` or `__set`.
- **`isOmitted()`** excludes a control (`setOmitted()`, or a disabled control with
  `omitted === null`); buttons and the `_form_` tracker are omitted.
  `setFixedValue()` = disabled + not omitted + value.
- **`setDefaults()` / `setDefaultValue()` on a submitted form only fill
  *disabled* controls**, which is why setting defaults after submission appears to
  "do nothing".

## Validation scope

`SubmitButton::setValidationScope(iterable)` accepts `Container`/`Control`
targets or component-name strings (resolved via `$form->getComponent()`); anything
else throws. `Container::validate($controls)` validates only that subset (`[]`
validates nothing). The scope also narrows `getValues()` and is exported to the
client as `data-nette-validation-scope` (plus `formnovalidate` on the button).

## The `Control` interface is the contract

`Control` declares what the framework calls on a control: value and state
(`setValue`, `getValue`, `loadHttpData`, `isFilled`, `isDisabled`, `isOmitted`),
validation (`getRules`, `validate`, `addError`, `getErrors`), and form and
rendering (`getForm`, `getHtmlName`, `getControl`, `getLabel`, `getOption`,
`setOption`). Code is written against `Control`; the few `instanceof BaseControl`
checks only enable optional extras (the caption for `%label`, the translated
description in the default renderer, the `form=` attribute in a detached Latte
form). The Latte `n:name` and `{input name:part}` paths call
`getControlPart()`/`getLabelPart()`, which the interface does not declare.
