# Control value semantics

Per-control quirks that are invisible from the `Control` interface and bite
during data loading (`setDefaults`) and reading (`getValues`).

## Choice controls: strict on defaults, lenient on submission

`ChoiceControl` / `MultiChoiceControl` enforce membership **asymmetrically**:

- **`setValue()` (and thus `setDefaults()`) throws** for a value outside
  `$items`, unless `checkDefaultValue(false)` is called first — the classic trap
  when filling a form from the DB before `setItems()` has the full list.
  `BackedEnum` values are unwrapped to their backing value first.
- **`loadHttpData()` does not validate at all.** The submitted key is stored raw;
  membership is enforced lazily by **`getValue()`, which filters at read time**:
  an unknown or per-item-disabled key reads as `null` (multi: dropped). Use
  `getRawValue()` to see what was actually submitted.

Keys pass through PHP array-key coercion (`key([(string) $v => null])`), so
numeric strings become ints. `setDisabled(array)` disables individual items (the
control itself stays enabled); since `isFilled()` builds on `getValue()`, a
selection of a disabled item counts as *not filled*. `MultiChoiceControl`
appends `[]` to `getHtmlName()`.

`SelectBox` adds prompt machinery (the prompt key is `''`, extended with tabs on
collision; for a required select it is rendered hidden+disabled) and its
constructor **auto-adds a closure condition + `Filled` rule** (message
`SelectBox::Valid`) guarding the "no prompt, nothing chosen" case — being a
non-exportable *condition*, it is skipped in the client export, per the
`exportRules` rules. `setItems()` accepts optgroups (nested arrays) and flattens
them for the underlying membership checks.

## TextBase: two values and a maxlength side-channel

`TextBase` keeps the coerced `$value` **and** a string `$rawValue`; rendering
uses `$rawValue` / the translated `emptyValue`. `getValue()` maps a value equal
to the (trimmed, translated) `emptyValue` to `''`, and `setNullable()` further
maps `''` to `null`. `emptyValue` is exported to the client as
`data-nette-empty-value`.

`TextBase::addRule()` mirrors the value into the DOM: a `Length`/`MaxLength`
rule also sets the `maxlength` attribute — but **only while all existing rules
are still client-exportable**; once a non-exportable non-branch rule (a filter)
precedes it, the attribute shortcut is disabled, consistent with the export
`break` (the browser must not hard-enforce a limit the filter may change).

## UploadControl: value comes only from HTTP

`setValue()` is a **no-op** — uploads cannot be defaulted; `getValue()` returns
the `FileUpload`(s), or a dummy `FileUpload(null)` when nothing was uploaded
(`setNullable()` switches that to `null`). The constructor auto-adds rules: the
`isOk()` check wrapped in `addCondition(true)` (a `:static` condition,
explicitly so the non-exportable callable doesn't `break` the export of the
sibling `MaxFileSize` rule), `MaxFileSize` from `upload_max_filesize`, and for
`multiple` a `MaxLength` capped by `max_file_uploads`. A monitor throws unless
the form method is POST and stamps `enctype="multipart/form-data"` on the form
prototype. `getHtmlName()` appends `[]` when multiple.

## DateTimeControl: normalization funnel

Every inbound value (`setValue`, rule args) goes through `normalizeValue()`:
string/timestamp/`DateTimeInterface` → `DateTimeImmutable`, then **truncated by
type** (date: time zeroed; time: date collapsed to 0001-01-01, seconds dropped
unless `withSeconds`). `loadHttpData()` swallows parse errors into `null`.
`getValue()` re-shapes by `setFormat()`: object (default), timestamp, or a
`format()` string. `getControl()` derives `min`/`max` attributes from `Min`/
`Max`/`Range` rules — again stopping at the first non-exportable rule. The
validator-side special-casing lives in `docs/internals/validation.md`.
