# Container tree & HTTP data flow

`Form extends Container extends Nette\ComponentModel\Container`. The tree and the
way submitted data reaches each control are one emergent model.

## Data is pulled, per control, from one flat array

There is **no central distribution** of submitted data. Each control pulls its own
value, lazily, driven by the component monitor:

- `BaseControl`'s constructor registers `monitor(Form::class, …)`; when a
  **non-disabled** control is attached to an **anchored, submitted** form it calls
  `loadHttpData()` (`BaseControl::loadHttpData` →
  `setValue(getHttpData(Form::DataText))`). `loadHttpData` is the template-method
  hook overridden by `SubmitButton`, `CsrfProtection`, etc.
- `BaseControl::getHttpData()` asks `Form::getHttpData($type, $htmlName)`, where
  `$htmlName` is the full bracketed path (`getHtmlName()` →
  `Helpers::generateHtmlName(lookupPath(Form::class))`); an explicitly set `name`
  attribute overrides the generated one, and setting it on a submitted form
  re-triggers `loadHttpData()`.
- `Form::getHttpData()` **lazily** fills `Form::$httpData` **once**, from
  `receiveHttpData()`, and sets `$submittedBy = is_array($data)`. This is the only
  place `$httpData` is populated.
- `Helpers::extractHttpData()` walks that flat array by the path: it strips `]`,
  turns `.`→`_`, splits on `[`, then `Arrays::get`s down the keys. A trailing `[]`
  triggers per-element sanitization; `DataKeys` preserves keys, otherwise
  `array_values` renumbers.

**Sanitization is by data-type bit** (`Helpers::sanitize`): `DataText` normalizes
newlines only; `DataLine` collapses newlines to spaces and trims (single-line
inputs); `DataFile` passes only a real `FileUpload` (else `null`). An agent adding a
control picks the bit that matches; picking `DataText` for a single-line field
leaks newlines.

## Submission detection lives in `receiveHttpData`

`Form::receiveHttpData()` returns `null` (not submitted) unless **all** hold:

1. the HTTP method matches the form's method;
2. for POST, the request passes the **same-origin** check
   (`!crossOrigin && $request->isFrom(FetchSite::SameOrigin)`) — the actual
   Sec-Fetch-Site / cookie logic lives in **nette/http**, not here; Forms only calls
   `isFrom`. `allowCrossOrigin()` disables this (and token protection via
   `CsrfProtection`/`addProtection()` is deprecated in favor of it);
3. the **`_form_` tracker** (present only for a **named** form) equals the form's
   name. An unnamed form has no tracker, so detection rests on method + data alone;
   for GET an **empty query string** already means "not submitted".

`submittedBy` starts as the bool `true` and is **narrowed to a `SubmitButton`
instance** by `SubmitButton::loadHttpData()` when that button is filled — that is
how "which button submitted" is known.

## `fireEvents` order

`Form::fireEvents()` runs a fixed sequence: return if not submitted; validate only
if there are no errors yet; then `$submittedBy->onClick`/`onInvalidClick` (for a
`SubmitButton`), then `onSuccess` (if valid), then `onError` (if invalid), then
always `onSubmit`; a warning fires if nothing was handled. `invokeHandlers`
inspects each handler's first parameter type by reflection to pass `$form` / the
button / `getValues($type)` (a second parameter, if present, always gets
`getValues`), and **stops the chain the moment a handler invalidates the form**.

`Form::validate()` (override) pulls the validation scope from the clicked
`SubmitterControl`, runs `validateMaxPostSize()` (a form-level error when
`CONTENT_LENGTH` exceeds `post_max_size` — reusing the `MaxFileSize` message),
then delegates to `Container::validate($controls)`.

## Reading values back out of the tree

- **`getValues()` = `getUntrustedValues()` + guards.** It **throws** if called
  during validation (`validated === null`), warns if the form is invalid, applies
  the validation-scope narrowing, then delegates.
- **`getUntrustedValues()`** walks the component tree: non-omitted `Control`s
  contribute `getValue()` (with enum coercion against the target property type),
  nested `Container`s recurse. The return shape is `ArrayHash` by default, or
  `$mappedType` (`setMappedType`), or a class you pass — a **DTO class** is built by
  reflection (constructor with required params, else property assignment).
- **`isOmitted()`** controls exclusion (`setOmitted`, or a disabled control with
  `omitted === null`); the tracker, buttons, and CSRF field are omitted.
- **`setDefaults()` on a submitted form only fills *disabled* controls**
  (`onlyDisabled: form->isSubmitted()`), which is why setting defaults after submit
  appears to "do nothing" for normal fields.

## Validation scope

A `SubmitButton::setValidationScope(iterable)` accepts `Container`/`Control`
targets or component-name strings (resolved via `$form->getComponent()`); anything
else throws. `Form::validate()` passes them down; `Container::validate($controls)`
validates only that subset (`[]` validates nothing). The same scope also narrows
`getValues` (a container is included when any of its ancestors is in scope), and is
exported to the client as `data-nette-validation-scope` (plus `formnovalidate` on
the button).

## The `Control` contract is deliberately minimal — and not honored

`Control` declares only **five** methods: `setValue`, `getValue`, `validate`,
`getErrors`, `isOmitted`. In practice the framework requires far more of every
control (`getHtmlName`, `getControl`, `getLabel`, `getForm`, `getOption`,
`isFilled`, …), so it is written against `BaseControl` everywhere. The current state
papers over the gap with **`instanceof BaseControl` guards** (a handful of sites:
`Validator` for `%label`, the renderer's `translate`, `Form`, `Blueprint`, the Latte
runtime) and a **`method.notFound` ignore block in `phpstan.neon`** that enumerates
the "missing" interface methods. Treat "a control is a `BaseControl`" as the real,
if unstated, contract.
