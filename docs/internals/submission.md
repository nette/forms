# Submission detection

Whether a form was submitted, and with what data, is decided by a
`SubmissionSource` the form holds. Nothing else in the package touches the HTTP
request.

## The source and when it is fixed

- `SubmissionSource::receiveData(Form)` returns the raw data array, or `null` for
  "not submitted via this source". `prepare(Form)` is called once, from
  `Form::fireRenderEvents()`, so a source can install its marker fields before
  rendering.
- `Sources\HttpSource` reads the request (from globals unless one is passed to the
  constructor); `Sources\NullSource` is never submitted (render-only forms,
  `Blueprint`).
- **The default source is lazy and materializes on first need.**
  `Form::resolveSubmissionSource()` calls `createDefaultSource()` (an
  `HttpSource`) the first time anything asks: `isAnchored()`, `isSubmitted()`,
  `getSubmittedData()`, `allowCrossOrigin()`, rendering. Attaching any control
  asks (the `BaseControl` monitor calls `isAnchored()`/`isSubmitted()`), and so do
  `setDefaults()`/`setDefaultValue()`.
- **`setSubmissionSource()` is write-once and must precede all of that.** It
  throws when the default has already materialized or a source was set. Called on
  a form that already holds controls or repeaters, it reads the data at once and
  lets them load: repeaters first (they build their item containers, whose
  controls load on attach), then the enabled controls still attached to the form.
- **Reading the data is also a point of no return for `setMethod()`**, which
  throws once `getSubmittedData()` has run. With the default source, adding the
  first control already reads the data, so `setMethod()` belongs before the
  controls.
- `Form::getSubmittedData()` reads once and caches; `submittedBy` becomes `true`
  when the source returned an array. A form that is not anchored throws there.

## HttpSource rules

`HttpSource::receiveData()` returns `null` unless all hold:

1. the request method equals the form method;
2. for POST, the request is same-origin (`IRequest::isFrom(FetchSite::SameOrigin)`;
   the Sec-Fetch-Site logic lives in nette/http). `allowCrossOrigin()` switches
   the check off entirely; it is the only CSRF protection the package has.
   POST data is `mergeTree(post, files)`;
3. for GET, the query string is not empty: an **unnamed** GET form counts as
   submitted by any query parameter;
4. for a **named** form, the `_form_` field (`HttpSource::TrackerId`) equals the
   form name. The tracker is a persistent, omitted `HiddenField` installed by
   `prepare()`, i.e. **only at render time**; detection reads the raw data and
   does not need the component. An unnamed form has no tracker.

`Form::allowCrossOrigin()` resolves the source and throws unless it is an
`HttpSource`.

## Descendants anchored elsewhere

A descendant whose data comes from somewhere else (the presenter-bound form of
nette/application) returns `null` from `createDefaultSource()`; until it gets a
source, `isAnchored()` is false, controls do not load, and
`getSubmittedData()` throws. The other seam is overriding `isAnchored()` together
with `receiveHttpData()`; `Form::receiveHttpData()` only delegates to the source
and throws when a descendant overrides `isAnchored()` without supplying data.

`submittedBy` narrows from `true` to the button that submitted:
`SubmitButton::loadHttpData()` and `ImageButton::loadHttpData()` call
`Form::setSubmittedBy()` when they find their value in the data. `Form::reset()`
clears it, erases all values and resets repeaters.
