# Control value semantics

Per-control quirks that are invisible from the `Control` interface and bite
during data loading (`setDefaults`) and reading (`getValues`).

## Choice controls: strict on defaults, lenient on submission

`ChoiceControl` / `MultiChoiceControl` enforce membership **asymmetrically**:

- **`setValue()` (and thus `setDefaults()`) throws** for a value outside
  `$items`, unless `checkDefaultValue(false)` is called first: the classic trap
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
appends `[]` to `getHtmlName()`. `CheckboxList` also accepts one comma-joined
string, the shape the client sends for a GET form with `data-nette-compact`.

`SelectBox` adds prompt machinery (the prompt key is `''`, extended with tabs on
collision; for a required select it is rendered hidden+disabled) and its
constructor **auto-adds a closure condition + filled rule** (message id
`SelectBox::Valid`) guarding the "no prompt, nothing chosen" case; the closure
condition is server-only and skipped by the client export. `setItems()` accepts
optgroups (nested arrays) and flattens them for the membership checks.

## HTML validation attributes: the validator offers, the control decides

`BaseControl::getControl()` renders, from the attributes the rules declare
(`docs/internals/validation.md`), only the names returned by
`getRuleAttributeNames()`: none by default, `maxlength` for `TextBase`,
`accept` for `UploadControl`, `min`/`max` for `DateTimeControl`. `TextInput`
adds `type`, then by the effective type (the prototype's, else the declared
one): `min`/`max` for number, range and date/time types, `pattern` for text-like
types, `inputmode` for plain text. So `addInteger()` renders `type="number"` with
`min`/`max` from a range rule, while `addFloat()` stays `type="text"` with
`inputmode="decimal"` and no bounds. `DateTimeControl` re-formats its `min`/`max`
with `formatHtmlValue()`.

## TextBase: two values and the own maxlength

`TextBase` keeps the coerced `$value` **and** a string `$rawValue`; rendering
uses `$rawValue` / the translated `emptyValue`. `getValue()` maps a value equal
to the (trimmed, translated) `emptyValue` to `''`, and `setNullable()` further
maps `''` to `null`. `emptyValue` is exported to the client as
`data-nette-empty-value`.

The control's own `maxlength` attribute (`setMaxLength()`, the `$maxLength`
factory parameter) is enforced by `TextBase::validate()` against
`$submittedText`, the text as it came from HTTP before any filter, after the
rules and only when they passed, so a forged request cannot bypass it. A value
not loaded from HTTP is not checked. It is no rule: it exports nothing to the
client (the attribute itself limits the input), its message is the
`LengthValidator` one, and the server counts characters where the browser counts
UTF-16 units, so the server is the more lenient side.

`TextInput::setSuggestions()` renders a `<datalist>` through `getDatalist()`;
renderers emit the datalists of rendered inputs before the form end, next to
unrendered hidden fields.

## UploadControl: value comes only from HTTP

`setValue()` is a **no-op**, uploads cannot be defaulted. `getValue()` returns the
`FileUpload`, or a dummy `FileUpload(null)` when nothing was uploaded
(`setNullable()` switches that to `null`); a **multiple** upload returns a list,
empty when nothing was uploaded, and cannot be nullable. The constructor
auto-adds rules: the server-only `isOk()` check, `MaxFileSize` from
`upload_max_filesize`, and for `multiple` a `MaxLength` capped by
`max_file_uploads` (message id `UploadControl::MaxFiles`). `addRule()` for
`MaxFileSize`/`FileSizeValidator` or `MaxLength` replaces the existing one and
warns when it exceeds the ini limit. A monitor throws unless the form method is
POST and stamps `enctype="multipart/form-data"` on the form prototype.
`getHtmlName()` appends `[]` when multiple.

## DateTimeControl: normalization funnel

Every inbound value (`setValue`, rule args) goes through `normalizeValue()`:
string/timestamp/`DateTimeInterface` → `DateTimeImmutable`, then **truncated by
type** (date: time zeroed; time: date collapsed to 0001-01-01, seconds dropped
unless `withSeconds`). `loadHttpData()` swallows parse errors into `null`.
`getValue()` re-shapes by `setFormat()`: object (default), timestamp, or a
`format()` string. The validator-side special-casing lives in
`docs/internals/validation.md`.

## HiddenField

A `HiddenField` constructed with a persistent value unregisters its monitor and
never loads submitted data (the `_form_` tracker is one). Its `addError()` adds
to the form errors, and it renders no `data-nette-rules`, so its rules are
server-only.
