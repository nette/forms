# Repeater

`Repeater` (`src/Forms/Repeater.php`) is a `Container` of item containers named
`0`, `1`, …, each built by calling the factory closure on a fresh `Container`.
It accepts only item containers (`addComponent()` throws for anything else);
controls belong in the factory. Server, renderers and the client script share one
contract, so a change on one side usually needs the others.

## Items on the server

- **Not submitted:** `setBounds()` immediately builds items up to the default
  count (trimming to the maximum). `addRepeater()` calls it before attaching, so
  the default items exist from the start.
- **Submitted:** items are built **lazily** by `ensureLoaded()`, on validation,
  reading or setting values, rendering, `setSubmissionSource()`, or accessing an
  item by name (`createComponent()`), so bounds set after `addRepeater()` still
  apply. `loadHttpData()` counts the submitted items (`getSubmittedData()`,
  renumbered to a list) and rebuilds exactly that many, **never padding** to the
  minimum. The build is capped at the maximum (or `ItemsLimit` when unbounded) plus
  `OverflowReserve`, so a forged request cannot run the factory without limit,
  while a genuine small overshoot stays visible and removable.
- **Count errors** come from the raw submitted count in `validate()`, before the
  items are validated, so `onValidate` handlers see them. `addError()` stores the
  error on the repeater (`getOwnErrors()`) and also on the form, since a container
  has no place for errors in the rendered form.
- `setValues()` treats values as a list (keys ignored) and grows the items to
  their count, with `erase` also shrinks them; `setDefaults()` on a submitted form
  fills only disabled controls and changes no count.
- `Form::reset()` calls `Repeater::reset()`: submitted state dropped, default items
  rebuilt.
- `getValues()` yields the items indexed from 0. `Repeater::setMappedType()` maps
  **each item** to the class and returns a list, unlike
  `Container::setMappedType()`, which maps the container itself.

## Detached items

`createDetachedItem()` makes an item that knows its parent while the repeater does
not list it: it lives in the real form (translator, HTML names and ids, control
monitors) but is not validated, has no values, and is reset to the factory
defaults after attaching, so it never shows submitted data. Two kinds exist:

- **the template**, created when the repeater is attached to a form and dropped on
  detach, named by a **depth-specific placeholder** `__index<depth>__` (depth = number
  of enclosing repeaters), so nested templates keep distinct placeholders;
- **the rows offered up to the minimum after a submission with too few items**,
  created anew by each `renderEach()` call.

A repeater inside a detached item never loads submitted data.

## Rendering contract

`renderEach()` is the core all renderers use: it prints every item through the
renderer callback, the offered rows, and the template inside `<template>`, and
resets the `rendered` option of the template controls. Each item is wrapped in an
element carrying `data-repeater-index` with its name (the placeholder in the
template). `renderItemEnd()` prints, inside the item element, the hidden fields
the callback did not render, so they are cloned and removed with the item, and,
for detached items only, the datalists of rendered inputs, which the form end does
not know. The container element carries `data-nette-repeater` (the path),
`-min` and `-max` (`getContainerAttributes()`); the add button carries
`data-nette-repeater-add="<path>"`, the remove button `data-nette-repeater-remove`.

- `render()` / `renderItems()`: container prototype + item prototype around each
  item; `DefaultFormRenderer::renderRepeater()` uses `renderItems()` inside its
  own `repeater.container` wrapper.
- Latte `{repeater name}…{/repeater}`: the body is the item content, rendered by
  `render()`.
- `n:repeater="name"` on an element: the element becomes the container; its content
  is the item content, unless it holds an element with `n:repeater-item`, which
  then is the item wrapper itself (`renderEach()`); `n:repeater-item` inside
  `{repeater}` is a compile error. `n:repeater-add="name"` resolves the name in
  the current scope, `n:repeater-remove` uses the innermost repeater being rendered.
- Item scopes are entered with `bindsForm: false`, so `$form` keeps the form.

## Client side

`src/assets/repeater.ts` clones the template, finds the item element by
`data-repeater-index`, and **replaces the placeholder in every attribute** of the
clone, including nested `<template>` contents; elements the HTML parser moved out
of the item (a `<datalist>` in a table row) are put back into it. The new index
is above every existing one. Bounds are enforced by disabling the buttons, and
`repeater:add` / `repeater:remove` are cancelable events (`repeater:added`
follows an addition). After a change the form toggles are recomputed; validation
needs nothing, because it walks `form.elements` at submit time.

The end-to-end specs use HTML fixtures rendered by PHP; after changing the
rendered markup run `php tests/netteForms/fixtures/generate.php`
(`tests/Forms.Latte/repeater.fixtures.phpt` fails while they are outdated).
