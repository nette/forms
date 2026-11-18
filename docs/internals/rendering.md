# Rendering

Two rendering paths: the programmatic `DefaultFormRenderer` and the Latte runtime.
Both draw HTML from the same control **prototypes** (`getControl()`/`getLabel()`),
so custom markup ultimately flows through those `Html` elements. Both duplicate
the same form-level duties, and a change to one belongs in the other:

- **Render events first.** `Form::fireRenderEvents()` runs once per form:
  `SubmissionSource::prepare()` (which installs the `_form_` tracker), then
  `beforeRender()` and `onRender`. `render()`/`__toString()` and the Latte
  `begin()` call it; a hand-written renderer must too.
- **The `rendered` option.** Both reset every control's `rendered` option at the
  form begin; `getControl()` sets it. The form end emits every hidden field not yet
  rendered and the `<datalist>` of every rendered `TextInput` with suggestions.
  Repeater items are an exception, see `docs/internals/repeater.md`.
- **GET forms.** The query string is stripped from `action` and its parameters are
  re-emitted as hidden inputs, skipping names that collide with form components:
  the default renderer emits them right after the start tag, the Latte runtime
  before the end tag.

## DefaultFormRenderer: the `$wrappers` map

`DefaultFormRenderer` is table-oriented and driven by a nested `$wrappers` map:
`section => key => Html|string|null` (e.g. `controls.container = 'table'`,
`pair.container = 'tr'`, `label.container = 'th'`, `control.container = 'td'`,
`repeater.container`, plus per-state keys like `pair.required`/`.error`/`.odd`
and per-input-type control classes). `render()` fans out to
`renderBegin`/`renderErrors`/`renderBody`/`renderEnd`; `renderBody` walks groups →
`renderControls` → `renderPair` (label + control), with buttons aggregated into
`renderPairMulti`. Wrapper elements are **cloned** per use (`getWrapper`) so
mutating one rendered element never leaks into the template.

`renderControls()` walks a container by its component tree, so a repeater
without items is still rendered. The first control met inside a repeater renders
the whole outermost not-yet-rendered repeater through `renderRepeater()`, which
closes the current controls table, prints the items and the template, and
remembers the repeater for the rest of this render.

## Latte runtime: the stacks

`Bridges/FormsLatte/Runtime` keeps three **parallel** stacks: `$stack` (the current
form/container scope), `$detachedIds` (the detached form id per level, `null`
when not detached) and `$formBindings` (whether the level is bound to the
template's `$form` variable; `{formContainer}` and repeater items are not), plus a
separate stack of repeaters being rendered. `begin()` pushes and, for a `Form`,
fires the render events and resets `rendered`. `end()` pops the three and returns
the nearest remaining level bound to `$form`, which the compiled `{/form}`
assigns back to `$form` (at the top level `$form` keeps the closed form). `get()`
resolves an element from the current scope and, **when a detached id is active,
stamps `form="<id>"` on a `BaseControl` via `setHtmlAttribute()`**, i.e. on its
prototype, so the attribute stays for any later rendering of that control.

`FormNode` compiles `{form}` in three modes:

- **normal**: `renderFormBegin … body … renderFormEnd`.
- **`{form scope name}`** (and the deprecated `{formContext}`): renders the body
  **only**, no `<form>` tag; used to (re-)enter a form or container without markup
  (e.g. an AJAX snippet). It takes no attributes (a compile error otherwise).
  Inside an open form a name resolves to a container of it, otherwise to a
  component of the UI control.
- **`{form detached name}`**: emits begin and end **before** the body, i.e. an
  empty `<form id=…></form>` carrying the unrendered hidden fields including the
  tracker, then the body, whose controls are linked to it by `form=`. This lets a
  page form legitimately contain an independent inner form. A detached form
  **must** have an id (else `InvalidStateException`); nested containers inherit the
  id, a nested `Form` starts fresh.

`<form n:name>` behaves like the normal mode. The invariant a theme/renderer must
respect: **the `{form}` machinery owns the `<form>` begin/end** in all modes;
nothing else may emit the `<form>` tag, or detached and nested forms break.

`{formContainer}` is deprecated in favour of `{form scope}`, `{formPrint}` and
`{formClassPrint}` in favour of `Blueprint::latte()` / `Blueprint::dataClass()`.
The repeater tags are described in `docs/internals/repeater.md`.
