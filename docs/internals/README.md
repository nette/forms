# Forms internals

How `nette/forms` works underneath, for agents editing it. Split by the natural
seams; each file is self-contained.

- **[container-and-data.md](container-and-data.md)** — the Form/Container/Control
  tree, how submitted HTTP data is read (the flat pull model), submission
  detection, `fireEvents`, `getValues`, validation scope, and the deliberately
  minimal `Control` contract.
- **[validation.md](validation.md)** — the `Rules`/`Rule` tree, evaluation order,
  and the sharp traps (`:valid`, filter re-application, message override keying).
- **[rendering.md](rendering.md)** — `DefaultFormRenderer` wrappers and the Latte
  runtime, including the `{form detached}`/`{form scope}` machinery.

> **Version scope.** This describes the current **v3.x** release line (server-side
> flat data model, string-op validation rules, a 5-method `Control` interface). A
> v4 redesign of the data flow (layered pull, submission sources) and the
> validation core (validator objects, message identifiers) is planned and will
> change several of the mechanisms below; treat this as the v3.x source of truth
> until then.
