# Rendering (v3.x)

Two rendering paths: the programmatic `DefaultFormRenderer` and the Latte runtime.
Both draw HTML from the same control **prototypes** (`getControl()`/`getLabel()`),
so custom markup ultimately flows through those `Html` elements.

## DefaultFormRenderer: the `$wrappers` map

`DefaultFormRenderer` is table-oriented and driven by a nested `$wrappers` map:
`section => key => Html|string|null` (e.g. `controls.container = 'table'`,
`pair.container = 'tr'`, `label.container = 'th'`, `control.container = 'td'`, plus
per-state keys like `pair.required`/`.error`/`.odd` and per-input-type control
classes). `render()` fans out to `renderBegin`/`renderErrors`/`renderBody`/`renderEnd`;
`renderBody` walks groups → `renderControls` → `renderPair` (label + control), with
buttons aggregated into `renderPairMulti`. Wrapper elements are **cloned** per use
(`getWrapper` clones an `Html`, else builds one from a tag string) so mutating one
rendered element never leaks into the template. This renderer is stable/legacy —
new rendering work goes through Latte.

## Latte runtime: the `$stack` / `$detachedIds` machinery

`Bridges/FormsLatte/Runtime` keeps two **parallel** stacks: `$stack` (the current
form/container scope) and `$detachedIds` (the detached form id per stack level,
`null` when not detached). `begin()` pushes and, for a `Form`, fires the render
events; `end()` pops both. `get()` resolves an element from the current scope and —
**this is the detached-form wiring** — if a detached id is active and the element is
a `BaseControl`, sets `setHtmlAttribute('form', $id)` on it. `isNested()` is just
`(bool) $stack`.

`FormNode` compiles the `{form}` family in three modes:

- **normal** — wraps the body in `renderBegin … body … renderEnd`.
- **`{form scope name}`** (and the deprecated `{formContext}`) — renders the body
  **only**, no `<form>` tag; used to (re-)enter a form/container without emitting
  markup (e.g. an AJAX snippet). It takes **no arguments** (a compile error
  otherwise). `isNested()` decides whether it enters a container or an existing UI
  form.
- **`{form detached name}`** — emits `renderBegin . renderEnd` **before** the body,
  i.e. an empty `<form id=…></form>` (carrying hidden fields incl. the tracker),
  and the body is rendered **after** it. The body's controls are not physically
  nested in that form; each is linked back to it by the `form="<id>"` attribute that
  `get()` stamps on. This exploits HTML5's out-of-tree `form=` association so a page
  form can legitimately contain an independent inner `<form>`. A detached form
  **must** have a non-empty id (else `InvalidStateException`), and nested containers
  inherit the id while a nested `Form` starts fresh.

The invariant a theme/renderer must respect: **the `{form}` machinery owns the
`<form>` begin/end** across all three modes — nothing else may emit the `<form>` tag,
or detached and nested forms break.
