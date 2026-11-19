# Upgrading nette/forms

## 4.0.0

- **Removed** `Nette\Forms\Controls\CsrfProtection` (deprecated since 3.3.0): the form's built-in same-origin protection replaces it (460f1b97)
- **Removed** `Nette\Forms\Form::PROTECTOR_ID` (alias since 3.1.11): the token field is gone together with `Nette\Forms\Controls\CsrfProtection` (460f1b97)
- **Removed** `Nette\Forms\Form::ProtectorId` (marked @internal): the token field is gone together with `Nette\Forms\Controls\CsrfProtection` (460f1b97)
- **Removed** `Nette\Forms\Form::initialize()`: the request is created from globals lazily for each form; to use another request, call `$form->setSubmissionSource(new Nette\Forms\Sources\HttpSource($request))` (50072769, 085180c2)
- **Removed** `Nette\Forms\Form::$httpRequest` (marked @internal): inject the request with `$form->setSubmissionSource(new Nette\Forms\Sources\HttpSource($request))` (085180c2)
- **Removed** `Nette\Forms\Form::$crossOrigin`: the flag lives in the submission source; call `Nette\Forms\Form::allowCrossOrigin()`; affects subclasses reading or writing the property (085180c2)
- **Removed** the Latte provider `$this->global->formsStack`: use `$this->global->forms`; `Nette\Bridges\FormsLatte\Runtime::begin()` no longer takes the `$global` argument (a97e7164)
- **Removed** `Nette.webalize()` from `netteForms.js`: there is no replacement (1136b370)
- **Removed** the files `src/assets/netteForms.js` and `src/assets/netteForms.min.js` from the Composer package: install the npm package `nette-forms` or load it from a CDN (1d900723)
- **Renamed** `Nette\Forms\IControl` → `Nette\Forms\Control` (alias since 3.1.0); the old name was removed (a44343c3)
- **Renamed** `Nette\Forms\IFormRenderer` → `Nette\Forms\FormRenderer` (alias since 3.1.0); the old name was removed (a44343c3)
- **Renamed** `Nette\Forms\ISubmitterControl` → `Nette\Forms\SubmitterControl` (alias since 3.1.0); the old name was removed (a44343c3)
- **Renamed** `Nette\Forms\Form::TRACKER_ID` → `Nette\Forms\Sources\HttpSource::TrackerId` (alias since 3.1.11); the old name was removed (085180c2)
- **Renamed** `Nette\Forms\Form::TrackerId` → `Nette\Forms\Sources\HttpSource::TrackerId` (marked @internal); the old name was removed (085180c2)
- **Renamed** `Nette\Forms\Form::DataKeys` → `Nette\Forms\Form::DataArray`; using the old name triggers `E_USER_DEPRECATED` (3cf27004)
- **Deprecated** `Nette\Forms\Form::getHttpData()`: use `Nette\Forms\Form::getSubmittedData()` for all submitted data, or `Nette\Forms\Controls\BaseControl::getSubmittedValue()` inside a control; calling it triggers `E_USER_DEPRECATED` (8a4c3d3a)
- **Deprecated** `Nette\Forms\Controls\BaseControl::getHttpData()`: use `Nette\Forms\Controls\BaseControl::getSubmittedValue()`, which takes a key below the control instead of the tail of the HTML name; affects custom controls; calling it triggers `E_USER_DEPRECATED` (8a4c3d3a)
- **Deprecated** `Nette\Forms\Helpers::exportRules()`: use `Nette\Forms\ClientRuleExporter::export()`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `Nette\Forms\Validator::validateEqual()`: use `Nette\Forms\Validation\EqualValidator`; calling it triggers `E_USER_DEPRECATED` (2dc1dfb8)
- **Deprecated** `Nette\Forms\Validator::validateNotEqual()`: use `Nette\Forms\Validation\NotEqualValidator`; calling it triggers `E_USER_DEPRECATED` (2dc1dfb8)
- **Deprecated** `Nette\Forms\Validator::validateFilled()`: use `Nette\Forms\Validation\FilledValidator`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `Nette\Forms\Validator::validateBlank()`: use `Nette\Forms\Validation\BlankValidator`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `Nette\Forms\Validator::validateRange()`: use `Nette\Forms\Validation\RangeValidator`; calling it triggers `E_USER_DEPRECATED` (2dc1dfb8)
- **Deprecated** `Nette\Forms\Validator::validateMin()`: use `Nette\Forms\Validation\RangeValidator` with the lower bound; calling it triggers `E_USER_DEPRECATED` (2dc1dfb8)
- **Deprecated** `Nette\Forms\Validator::validateMax()`: use `Nette\Forms\Validation\RangeValidator` with the upper bound; calling it triggers `E_USER_DEPRECATED` (2dc1dfb8)
- **Deprecated** `Nette\Forms\Validator::validateLength()`: use `Nette\Forms\Validation\LengthValidator`, or `Nette\Forms\Validation\ItemCountValidator` for arrays; calling it triggers `E_USER_DEPRECATED` (2dc1dfb8)
- **Deprecated** `Nette\Forms\Validator::validateMinLength()`: use `Nette\Forms\Validation\LengthValidator`, or `Nette\Forms\Validation\ItemCountValidator` for arrays; calling it triggers `E_USER_DEPRECATED` (2dc1dfb8)
- **Deprecated** `Nette\Forms\Validator::validateMaxLength()`: use `Nette\Forms\Validation\LengthValidator`, or `Nette\Forms\Validation\ItemCountValidator` for arrays; calling it triggers `E_USER_DEPRECATED` (2dc1dfb8)
- **Deprecated** `Nette\Forms\Validator::validateEmail()`: use `Nette\Forms\Validation\EmailValidator`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `Nette\Forms\Validator::validateUrl()`: use `Nette\Forms\Validation\UrlValidator`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `Nette\Forms\Validator::validatePattern()`: use `Nette\Forms\Validation\PatternValidator`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `Nette\Forms\Validator::validatePatternCaseInsensitive()`: use `Nette\Forms\Validation\PatternValidator` with `caseInsensitive: true`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `Nette\Forms\Validator::validateEnum()`: use `Nette\Forms\Validation\EnumValidator`; calling it triggers `E_USER_DEPRECATED` (2dc1dfb8)
- **Deprecated** `Nette\Forms\Validator::validateNumeric()`: use `Nette\Forms\Validation\NumericValidator`; calling it triggers `E_USER_DEPRECATED` (2dc1dfb8)
- **Deprecated** `Nette\Forms\Validator::validateInteger()`: use `Nette\Forms\Validation\IntegerValidator`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `Nette\Forms\Validator::validateFloat()`: use `Nette\Forms\Validation\FloatValidator`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `Nette\Forms\Validator::validateFileSize()`: use `Nette\Forms\Validation\FileSizeValidator`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `Nette\Forms\Validator::validateMimeType()`: use `Nette\Forms\Validation\MimeTypeValidator`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `Nette\Forms\Validator::validateImage()`: use `Nette\Forms\Validation\ImageValidator`; calling it triggers `E_USER_DEPRECATED` (6d002237)
- **Deprecated** `{formContext}`: use `{form scope name}`; the tag triggers `E_USER_DEPRECATED` when the template is compiled (5a297a04)
- **Deprecated** `{formContainer}`: use `{form scope name}`; the tag triggers `E_USER_DEPRECATED` when the template is compiled (5a297a04)
- **Deprecated** `{formPrint}`: call `Nette\Forms\Blueprint::latte($form)`; the tag triggers `E_USER_DEPRECATED` when the template is compiled (5a297a04)
- **Deprecated** `{formClassPrint}`: call `Nette\Forms\Blueprint::dataClass($form)`; the tag triggers `E_USER_DEPRECATED` when the template is compiled (5a297a04)
- **Deprecated** `{input}` without a comma between the name and the arguments: write `{input name, attr=val}`; the old syntax triggers `E_USER_DEPRECATED` (03dc58f2)
- **Deprecated** `{label}` without a comma between the name and the arguments: write `{label name, attr=val}`; the old syntax triggers `E_USER_DEPRECATED` (03dc58f2)
- **Deprecated** `Nette\Forms\Form::addProtection()` (deprecated since 3.3.0): remove the call, the form's built-in same-origin protection is sufficient; calling it triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::EQUAL` (alias since 3.1.11): use `Nette\Forms\Form::Equal`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::IS_IN` (alias since 3.1.11): use `Nette\Forms\Form::IsIn`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::NOT_EQUAL` (alias since 3.1.11): use `Nette\Forms\Form::NotEqual`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::IS_NOT_IN` (alias since 3.1.11): use `Nette\Forms\Form::IsNotIn`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::FILLED` (alias since 3.1.11): use `Nette\Forms\Form::Filled`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::BLANK` (alias since 3.1.11): use `Nette\Forms\Form::Blank`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::REQUIRED` (alias since 3.1.11): use `Nette\Forms\Form::Required`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::VALID` (alias since 3.1.11): use `Nette\Forms\Form::Valid`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::SUBMITTED` (alias since 3.1.11): use `Nette\Forms\Form::Submitted`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::MIN_LENGTH` (alias since 3.1.11): use `Nette\Forms\Form::MinLength`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::MAX_LENGTH` (alias since 3.1.11): use `Nette\Forms\Form::MaxLength`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::LENGTH` (alias since 3.1.11): use `Nette\Forms\Form::Length`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::EMAIL` (alias since 3.1.11): use `Nette\Forms\Form::Email`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::PATTERN` (alias since 3.1.11): use `Nette\Forms\Form::Pattern`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::PATTERN_ICASE` (alias since 3.1.11): use `Nette\Forms\Form::PatternInsensitive`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::INTEGER` (alias since 3.1.11): use `Nette\Forms\Form::Integer`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::NUMERIC` (alias since 3.1.11): use `Nette\Forms\Form::Numeric`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::FLOAT` (alias since 3.1.11): use `Nette\Forms\Form::Float`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::MIN` (alias since 3.1.11): use `Nette\Forms\Form::Min`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::MAX` (alias since 3.1.11): use `Nette\Forms\Form::Max`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::RANGE` (alias since 3.1.11): use `Nette\Forms\Form::Range`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::COUNT` (alias since 3.1.11): use `Nette\Forms\Form::Count`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::MAX_FILE_SIZE` (alias since 3.1.11): use `Nette\Forms\Form::MaxFileSize`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::MIME_TYPE` (alias since 3.1.11): use `Nette\Forms\Form::MimeType`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::IMAGE` (alias since 3.1.11): use `Nette\Forms\Form::Image`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::MAX_POST_SIZE` (alias since 3.1.11): use `Nette\Forms\Form::MaxPostSize`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::GET` (alias since 3.1.11): use `Nette\Forms\Form::Get`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::POST` (alias since 3.1.11): use `Nette\Forms\Form::Post`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::DATA_TEXT` (alias since 3.1.11): use `Nette\Forms\Form::DataText`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::DATA_LINE` (alias since 3.1.11): use `Nette\Forms\Form::DataLine`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::DATA_FILE` (alias since 3.1.11): use `Nette\Forms\Form::DataFile`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Form::DATA_KEYS` (alias since 3.1.11): use `Nette\Forms\Form::DataArray`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c, 3cf27004)
- **Deprecated** `Nette\Forms\Controls\SelectBox::VALID` (alias since 3.1.11): use `Nette\Forms\Controls\SelectBox::Valid`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Controls\UploadControl::VALID` (alias since 3.1.11): use `Nette\Forms\Controls\UploadControl::Valid`; using the old name triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Container::addImage()` (deprecated since 3.1.0): use `Nette\Forms\Container::addImageButton()`; calling it triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Container::getUnsafeValues()` (alias since 3.1.10): use `Nette\Forms\Container::getUntrustedValues()`; calling it triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Controls\BaseControl::setAttribute()` (deprecated since 3.0.0): use `Nette\Forms\Controls\BaseControl::setHtmlAttribute()`; calling it triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Controls\TextInput::setType()` (deprecated since 3.0.0): use `Nette\Forms\Controls\TextInput::setHtmlType()`; calling it triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Controls\Checkbox::getSeparatorPrototype()` (deprecated since 3.1.0): use `Nette\Forms\Controls\Checkbox::getContainerPrototype()`; calling it triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Controls\SelectBox::addOptionAttributes()` (deprecated since 3.2.0): use `Nette\Forms\Controls\SelectBox::setOptionAttribute()` once per attribute; calling it triggers `E_USER_DEPRECATED` (98d5b73c)
- **Deprecated** `Nette\Forms\Controls\MultiSelectBox::addOptionAttributes()` (deprecated since 3.2.0): use `Nette\Forms\Controls\MultiSelectBox::setOptionAttribute()` once per attribute; calling it triggers `E_USER_DEPRECATED` (98d5b73c)
- **Signature** `Nette\Forms\Control` extends `Nette\ComponentModel\IComponent` and declares `loadHttpData()`, `isFilled()`, `isDisabled()`, `getRules()`, `addError()`, `getForm()`, `getHtmlName()`, `getControl()`, `getLabel()`, `setOption()` and `getOption()`; affects classes implementing it other than through `Nette\Forms\Controls\BaseControl` (794dc93f)
- **Signature** native return and parameter types were added, so a subclass overriding these methods or a class implementing `Nette\Forms\Control` must declare them: `Nette\Forms\Control::getValue()` (`mixed`), `Nette\Forms\Control::setValue()` (`static`), `Nette\Forms\Controls\BaseControl::setValue()`, `Nette\Forms\Controls\BaseControl::setDefaultValue()`, `Nette\Forms\Controls\BaseControl::setDisabled()` and `Nette\Forms\Controls\BaseControl::addRule()` (`static`), `Nette\Forms\Controls\BaseControl::getControl()` (`Html|string`), `Nette\Forms\Controls\BaseControl::getLabel()` (`string|Stringable|null $caption`, `Html|string|null`), `Nette\Forms\Controls\BaseControl::addCondition()` and `Nette\Forms\Controls\BaseControl::addConditionOn()` (`$validator`), `Nette\Forms\Controls\ChoiceControl::setItems()` and `Nette\Forms\Controls\MultiChoiceControl::setItems()` (`static`), `Nette\Forms\Controls\CheckboxList::getControlPart()`, `Nette\Forms\Controls\CheckboxList::getLabelPart()`, `Nette\Forms\Controls\RadioList::getControlPart()` and `Nette\Forms\Controls\RadioList::getLabelPart()` (`int|string|null $key`), `Nette\Forms\Controls\SubmitButton::getControl()` (`string|Stringable|null $caption`) (ec6d8bfc)
- **Signature** `Nette\Forms\Controls\BaseControl::$disabled` is typed `bool`; previously `bool|array`, and `Nette\Forms\Controls\ChoiceControl` and `Nette\Forms\Controls\MultiChoiceControl` keep the individually disabled items in the new property `$disabledChoices`; affects subclasses reading the property (64a83478)
- **Signature** `Nette\Forms\Form::addProtection()` returns `void` and does nothing; previously it added and returned a `Nette\Forms\Controls\CsrfProtection` (460f1b97)
- **Signature** `Nette\Forms\Rule`: the properties are `readonly` and set through the constructor; affects code creating a rule and assigning its properties, or modifying an existing rule (4429a66f)
- **Changed** the default validation messages use named placeholders such as `%min`, `%max`, `%count`, `%limit` and `%expected`; previously `%d` and `%s`; the translator receives a `Nette\Forms\Validation\Message`, whose string form is this template, and the values are substituted after translation; update translations keyed by the English text (2dc1dfb8, ef9151d1)
- **Changed** `Nette\Forms\Validator::$messages` is empty by default and holds only overrides, keyed by the operation (`Nette\Forms\Form::MinLength`) or the message identifier (`forms.length.min`); previously it held the default catalog; affects code reading the default messages from it (2dc1dfb8)
- **Changed** `Nette\Forms\Controls\SelectBox::Valid` has the value `forms.selectBox`; previously `:selectBoxValid`; affects code using the literal value (ef9151d1)
- **Changed** `Nette\Forms\Controls\UploadControl::Valid` has the value `forms.upload`; previously `:uploadControlValid`; affects code using the literal value (ef9151d1)
- **Changed** the `Nette\Forms\Form::MinLength`, `Nette\Forms\Form::MaxLength` and `Nette\Forms\Form::Length` rules on `Nette\Forms\Controls\CheckboxList`, `Nette\Forms\Controls\MultiSelectBox` and a multiple upload count the selected items and report messages about items; previously the messages spoke of characters (860a29e0)
- **Changed** `Nette\Forms\Rules::addRule()` replaces an existing rule with the same validator and argument; previously a duplicate was appended, e.g. to the built-in rule of `Nette\Forms\Container::addEmail()` (ede5e5bd)
- **Changed** `Nette\Forms\Rules::addRule()` and the conditions check the argument of a string operation when the rule is declared and throw `Nette\InvalidArgumentException` for a wrong one, e.g. `Nette\Forms\Form::Range` without two bounds or `Nette\Forms\Form::MaxFileSize` with a control; previously a `TypeError` was thrown during validation (053d15b1, 2dc1dfb8)
- **Changed** `Nette\Forms\Rule::$validator` of a filter added by `Nette\Forms\Rules::addFilter()` is a `Nette\Forms\Validation\CallbackFilter`; previously a `Closure`; affects code inspecting the rules, such as a custom renderer (4429a66f)
- **Changed** `Nette\Forms\Container::addComponent()` throws `Nette\InvalidStateException` for a component that is neither `Nette\Forms\Control` nor `Nette\Forms\Container`; previously it was added (e69f31b7)
- **Changed** `Nette\Forms\Container::getValues()` creates an object of a class whose constructor has any parameters through the constructor; previously only when some of them were required (481b4d75)
- **Changed** `Nette\Forms\Container::getValues()` and `Nette\Forms\Container::getUntrustedValues()` throw `Nette\InvalidStateException` for a value that has no constructor parameter or writable public property in the target class, and for a required parameter without a value; previously a dynamic property was created or an `Error` was thrown (481b4d75)
- **Changed** `Nette\Forms\Controls\UploadControl::getValue()` of a multiple upload returns an empty array when nothing was uploaded; previously an empty `Nette\Http\FileUpload` on a form that was not submitted (c7ea1357)
- **Changed** `Nette\Forms\Controls\UploadControl::setNullable()` throws `Nette\InvalidStateException` on a multiple upload; check for an empty array instead (c7ea1357)
- **Changed** a `Nette\Forms\Controls\TextBase` control enforces its `maxlength` attribute on the server and a longer submitted text adds an error, measured on the text as sent before filters; previously only the browser checked it (e9c04b3d, cc0e43a0)
- **Changed** `Nette\Forms\Controls\TextInput::addRule()` no longer writes the `type`, `inputmode`, `min`, `max` and `pattern` attributes into `getControlPrototype()`, they are derived from the rules when the control is rendered; reading them from the prototype before rendering returns nothing (435b31d8, cc0e43a0)
- **Changed** `Nette\Forms\Controls\TextBase::addRule()` no longer writes the `maxlength` attribute into `getControlPrototype()`, it is derived from the rules when the control is rendered (435b31d8)
- **Changed** `Nette\Forms\Controls\UploadControl::addRule()` no longer writes the `accept` attribute into `getControlPrototype()`, it is derived from the rules when the control is rendered (435b31d8)
- **Changed** `Nette\Forms\Container::addFloat()` renders a text input with `inputmode=decimal`; previously `type=number` with `step=any`, which rejected a decimal comma (cc0e43a0)
- **Changed** the hidden tracker field of a named form is added when the form is rendered and renders after the other fields; previously it was created in the constructor, so `$form[Nette\Forms\Sources\HttpSource::TrackerId]` exists only after `Nette\Forms\Form::fireRenderEvents()` (085180c2)
- **Changed** the npm package `nette-forms` ships `nette-forms.umd.js`, `nette-forms.umd.min.js` and the ES module `nette-forms.mjs` in its root and declares an exports map, so only the package root can be imported; previously it shipped `src/assets/netteForms.js` and `src/assets/netteForms.min.js`; replace deep imports and CDN paths (ca0b3471)

## 3.3.1

- **Signature** `Nette\Forms\Controls\ChoiceControl::setDisabled()`: the parameter is named `$state`; previously `$value`, so a named argument must be renamed (0ad7d698)
- **Signature** `Nette\Forms\Controls\MultiChoiceControl::setDisabled()`: the parameter is named `$state`; previously `$value`, so a named argument must be renamed (0ad7d698)
- **Changed** `Nette\Forms\Form::validateMaxPostSize()` now reports exceeded `post_max_size` with the message `Nette\Forms\Validator::$messages[Nette\Forms\Form::MaxPostSize]`; previously with the `Nette\Forms\Form::MaxFileSize` message; customize or translate the `Nette\Forms\Form::MaxPostSize` message if you changed the other one (10e964ba)

## 3.3.0

- **Removed** `Nette\Bridges\FormsLatte\FormMacros`: support for Latte 2 is gone, use Latte 3 and `Nette\Bridges\FormsLatte\FormsExtension` (2e4c12c0)
- **Removed** `Nette\Forms\Rendering\DataClassGenerator` (deprecated since 3.1.15): use `Nette\Forms\Blueprint::dataClass()`, which prints the generated class instead of returning it (39b0b9bf)
- **Removed** `Nette\Forms\Rendering\LatteRenderer` (deprecated since 3.1.15): use `Nette\Forms\Blueprint::latte()`, which prints the generated template instead of returning it (39b0b9bf)
- **Removed** the negated validator syntax `~Nette\Forms\Form::Filled` passed to `Nette\Forms\Rules::addRule()` outside a condition: use `Nette\Forms\Form::Blank`; a negated validator now throws `Nette\InvalidArgumentException`, previously it triggered `E_USER_DEPRECATED` and was converted (39b0b9bf)
- **Removed** the second `$default` argument of `Nette\Forms\Controls\BaseControl::getOption()` (deprecated since 3.2.0): write `getOption($key) ?? $default`; the argument is now silently ignored (39b0b9bf)
- **Removed** the second `$default` argument of `Nette\Forms\ControlGroup::getOption()` (deprecated since 3.2.0): write `getOption($key) ?? $default`; the argument is now silently ignored (39b0b9bf)
- **Removed** `true` as `$returnType` of `Nette\Forms\Container::getValues()` (deprecated since 3.2.0): pass `'array'`; `true` now throws a `TypeError` (39b0b9bf)
- **Replaced** the Latte provider `$this->global->formsStack` with `$this->global->forms`, an instance of `Nette\Bridges\FormsLatte\Runtime` exposing `get()`, `getScope()`, `begin()` and `end()`: code generated by custom Latte tags should use it; `$this->global->formsStack` is kept as a live reference to the internal stack and is meant to be dropped in the next major (00de98de, 6be39b74)
- **Deprecated** `Nette\Forms\Controls\BaseControl::$filled`: use `Nette\Forms\Controls\BaseControl::isFilled()`; reading the magic property triggers `E_USER_DEPRECATED` (b3795184)
- **Deprecated** `Nette\Forms\Controls\BaseControl::$htmlName`: use `Nette\Forms\Controls\BaseControl::getHtmlName()`; reading the magic property triggers `E_USER_DEPRECATED` (b3795184)
- **Deprecated** `Nette\Forms\Controls\BaseControl::$omitted`: use `Nette\Forms\Controls\BaseControl::isOmitted()` and `Nette\Forms\Controls\BaseControl::setOmitted()`; using the magic property triggers `E_USER_DEPRECATED` (b3795184)
- **Deprecated** `Nette\Forms\Controls\BaseControl::$options`: use `Nette\Forms\Controls\BaseControl::getOptions()`; reading the magic property triggers `E_USER_DEPRECATED` (b3795184)
- **Deprecated** `Nette\Forms\Controls\BaseControl::$caption`: use `Nette\Forms\Controls\BaseControl::getCaption()` and `Nette\Forms\Controls\BaseControl::setCaption()`; using the magic property triggers `E_USER_DEPRECATED` (b3795184)
- **Deprecated** `Nette\Forms\Form::$action`: use `Nette\Forms\Form::getAction()` and `Nette\Forms\Form::setAction()`; using the magic property triggers `E_USER_DEPRECATED` (b3795184)
- **Deprecated** `Nette\Forms\Form::$method`: use `Nette\Forms\Form::getMethod()` and `Nette\Forms\Form::setMethod()`; using the magic property triggers `E_USER_DEPRECATED` (b3795184)
- **Deprecated** `Nette\Forms\Form::$renderer`: use `Nette\Forms\Form::getRenderer()` and `Nette\Forms\Form::setRenderer()`; using the magic property triggers `E_USER_DEPRECATED` (b3795184)
- **Deprecated** `Nette\Forms\Controls\ChoiceControl::$selectedItem`: use `Nette\Forms\Controls\ChoiceControl::getSelectedItem()`; reading the magic property triggers `E_USER_DEPRECATED` (b3795184)
- **Deprecated** `Nette\Forms\Controls\MultiChoiceControl::$selectedItems`: use `Nette\Forms\Controls\MultiChoiceControl::getSelectedItems()`; reading the magic property triggers `E_USER_DEPRECATED` (b3795184)
- **Deprecated** `Nette\Forms\Controls\SubmitButton::$submittedBy`: use `Nette\Forms\Controls\SubmitButton::isSubmittedBy()`; reading the magic property triggers `E_USER_DEPRECATED` (b3795184)
- **Deprecated** `Nette\Forms\Controls\CsrfProtection`: the form's built-in same-origin protection is sufficient; the class is silently deprecated (ed66d79b)
- **Deprecated** `Nette\Forms\Form::addProtection()`: the form's built-in same-origin protection is sufficient; the method is silently deprecated (ed66d79b)
- **Deprecated** passing an object that is neither `stdClass` nor `Traversable` to `Nette\Forms\Container::setDefaults()`: pass an array, `stdClass` or `Traversable`; using it triggers `E_USER_DEPRECATED` (26b0e915)
- **Deprecated** `{form}` without a comma between the form name and its attributes: write `{form name, attr=val}`; the old syntax triggers `E_USER_DEPRECATED` (118561df)
- **Signature** `Nette\Forms\Container::getControls()`: returns `iterable` instead of `Iterator`, and the returned object is a repeatable `IteratorAggregate` wrapper; affects code calling `rewind()`/`current()`/`next()` on the result or testing it with `instanceof` (27f94902)
- **Changed** `Nette\Forms\Controls\CheckboxList::getControlPart()`, `Nette\Forms\Controls\CheckboxList::getLabelPart()`, `Nette\Forms\Controls\RadioList::getControlPart()` and `Nette\Forms\Controls\RadioList::getLabelPart()` now throw `Nette\InvalidArgumentException` for a key that is not among the items; previously they rendered an element for the invalid key (ab36c7a6)
- **Changed** a POST form is now considered submitted only when the request is same-origin according to the `Sec-Fetch-Site` header; previously the `SameSite` cookie heuristic of `Nette\Http\Request::isSameSite()` was used, so browsers that do not send `Sec-Fetch-Site` are judged differently; call `Nette\Forms\Form::allowCrossOrigin()` to accept cross-origin submissions (29a4c7ef)
- **Changed** `{formContext}` now throws a compile error when attributes are passed, because it renders no `<form>` tag; remove the attributes (d71c781c)

## 3.2.9

- **Signature** `Nette\Forms\Container::setDefaults()`: the first parameter is named `$values`; previously `$data`, so a named argument must be renamed (8ffd9b0d)
- **Signature** `Nette\Forms\Form::getAction()`: returns `string`; previously `string|Stringable`, so an action set as an object is returned converted to a string (8ffd9b0d)
- **Signature** native types were added to the remaining parameters, so a value of another type now throws a `TypeError`: `Nette\Forms\Controls\BaseControl::extensionMethod()` (`callable $callback`), `Nette\Forms\Container::extensionMethod()` (`callable $callback`), `Nette\Forms\Controls\BaseControl::getOption()` and `Nette\Forms\Controls\BaseControl::setOption()` (`string $key`), `Nette\Forms\Controls\BaseControl::getHttpData()` (`int $type`), `Nette\Forms\Rules::addCondition()` and `Nette\Forms\Rules::addConditionOn()` (`$validator`), `Nette\Forms\Helpers::createInputList()` (`$wrapper`), `Nette\Forms\Validator::validateFileSize()`, `Nette\Forms\Validator::validateMax()`, `Nette\Forms\Validator::validateMin()`, `Nette\Forms\Validator::validateMaxLength()` and `Nette\Forms\Validator::validateMinLength()` (7cbe717e)
- **Changed** `Nette\Forms\ControlGroup::add()` now throws a `TypeError` for an item that is not a control, container or iterable; previously `Nette\InvalidArgumentException` with a descriptive message; affects code catching it (7cbe717e)
- **Changed** `Nette\Forms\Controls\BaseControl::getErrors()`, `Nette\Forms\Container::getErrors()`, `Nette\Forms\Form::getErrors()` and `Nette\Forms\Form::getOwnErrors()` now return a list with consecutive keys; previously removing duplicates could leave gaps in the keys (b749e9c2)
- **Changed** `Nette\Forms\Controls\BaseControl::getTranslator()` returns `null` when translation is switched off; previously it returned `false`, which contradicted the declared return type (b749e9c2)

## 3.2.8

- **Changed** `Nette\Forms\Controls\CheckboxList::getControl()` and `Nette\Forms\Controls\RadioList::getControl()` return an empty element when the control has no items; previously an empty container was rendered (85c87e6c)

## 3.2.6

- **Changed** `Nette\Forms\Controls\MultiChoiceControl::getSelectedItems()` returns the items in the order of the selected values; previously in the order of the items definition (64f46249)
- **Changed** `netteForms.js` calls `e.stopImmediatePropagation()` when client-side validation fails; previously `e.stopPropagation()`, so other `submit` listeners registered on the same form are no longer invoked (f4b2a4ae)

## 3.2.5

- **Signature** `Nette\Forms\Controls\BaseControl::__call()` and `Nette\Forms\Container::__call()` no longer declare the return type `mixed`; affects subclasses overriding them (40afa807)
- **Changed** `Nette\Forms\Container::addEmail()` now creates the control with a 255 character limit (`maxlength` attribute and a length rule); previously no limit was set; pass a different `$maxLength` to change it (2aeabcad)

## 3.2.4

- **Changed** `Nette\Forms\Controls\Button` is now omitted from `Nette\Forms\Container::getValues()`; previously only `Nette\Forms\Controls\SubmitButton` was omitted and a plain button's value was included (e6153503)
- **Changed** `Nette\Forms\Container::getUntrustedValues()` converts a value mapped onto a property or constructor parameter typed as a backed enum with `from()`; previously the raw scalar was kept (0fe57bf2)
- **Changed** the methods of the exported `Nette` object in `netteForms.js` are now non-enumerable prototype methods of a class; previously own enumerable properties, so `Object.keys(Nette)`, `for..in` and `Nette.hasOwnProperty()` no longer see them (1a44a89e)
- **Changed** `Nette.validators.valid()` takes the form validator instance as its second argument; previously it used the module-level singleton; affects code calling the validator directly instead of through rule dispatch (aa109515)

## 3.2.3

- **Removed** `Nette.invalidNumberMessage`: a control with browser-detected invalid input now reports itself via the native `reportValidity()` (cee6166b)
- **Signature** `Nette\Forms\Controls\BaseControl::setDisabled()` and `Nette\Forms\Controls\BaseControl::setOmitted()`: the parameter is named `$state`; previously `$value`, so a named argument must be renamed (0cd5069a)
- **Signature** `Nette\Forms\Form::getAction()`: returns `string|Stringable` instead of `mixed`; affects subclasses overriding it (0cd5069a)
- **Signature** `Nette\Forms\Form::getHttpData()`: returns `string|array|Nette\Http\FileUpload|null` instead of `mixed`; affects subclasses overriding it (0cd5069a)
- **Signature** `Nette.compactCheckboxes()` takes the `FormData` object as a second argument and writes into it; previously it filled a hidden input on the form, which is no longer created (2614e132)
- **Changed** `Nette\Forms\Container::setDefaults()` also fills controls disabled with `setDisabled()` once the form was submitted; previously it set no values at all in that case (e227d87d)

## 3.2.2

- **Changed** `Nette\Forms\Controls\SelectBox::getControl()` renders the prompt `<option>` as hidden and disabled when the control is required; previously it was a normal selectable option (a20fdb1f)

## 3.2.1

- **Changed** `Nette\Forms\ControlGroup` is no longer `final` and can be extended again (67ed392a)

## 3.2.0

- **Replaced** `Nette\Forms\Controls\SelectBox::addOptionAttributes()` with `Nette\Forms\Controls\SelectBox::setOptionAttribute()`: the new method sets one attribute per call instead of merging an array, so call it once per attribute; the old name is silently deprecated (f3d3d565)
- **Replaced** `Nette\Forms\Controls\MultiSelectBox::addOptionAttributes()` with `Nette\Forms\Controls\MultiSelectBox::setOptionAttribute()`: the new method sets one attribute per call instead of merging an array, so call it once per attribute; the old name is silently deprecated (f3d3d565)
- **Deprecated** the second `$default` argument of `Nette\Forms\Controls\BaseControl::getOption()`: write `getOption($key) ?? $default`; passing it triggers `E_USER_DEPRECATED` (deprecated since 3.1.8) (d3204f2d)
- **Deprecated** the second `$default` argument of `Nette\Forms\ControlGroup::getOption()`: write `getOption($key) ?? $default`; passing it triggers `E_USER_DEPRECATED` (deprecated since 3.1.8) (d3204f2d)
- **Deprecated** `true` as `$returnType` of `Nette\Forms\Container::getValues()`: pass `'array'`; using it triggers `E_USER_DEPRECATED` (0a812bd6)
- **Deprecated** `Nette\Forms\Rendering\DataClassGenerator::generateCode()`: use `Nette\Forms\Blueprint::dataClass()`, which prints the code instead of returning it; calling it triggers `E_USER_DEPRECATED` (deprecated since 3.1.15) (f3d3d565)
- **Deprecated** `Nette\Forms\Rendering\LatteRenderer::render()`: use `Nette\Forms\Blueprint::latte()`, which prints the template instead of returning it; calling it triggers `E_USER_DEPRECATED` (deprecated since 3.1.15) (f3d3d565)
- **Signature** `Nette\Forms\Container::setDefaults()` and `Nette\Forms\Container::setValues()`: `$data` is typed `array|object` and the methods return `static`; passing `null`, previously treated as an empty array, now throws a `TypeError`, and an invalid type throws a `TypeError` instead of `Nette\InvalidArgumentException` (dffc07dd)
- **Signature** `Nette\Forms\Controls\BaseControl::setDisabled()`: `$value` is typed `bool`; under `strict_types=1` a non-bool value, previously cast, now throws a `TypeError` (dffc07dd)
- **Signature** `Nette\Forms\Controls\ChoiceControl::setDisabled()` and `Nette\Forms\Controls\MultiChoiceControl::setDisabled()`: `$value` is typed `bool|array` and the methods return `static`; any other value throws a `TypeError` (dffc07dd)
- **Signature** `Nette\Forms\Container::getUntrustedValues()`: `$returnType` is typed `string|object|null` and lost its default `Nette\Utils\ArrayHash::class`, so calling without arguments throws an `ArgumentCountError`; the return type is `object|array` (dffc07dd)
- **Signature** labels, captions and messages are typed `string|Stringable` (or `string|Stringable|null`) throughout, so a non-`Stringable` object now throws a `TypeError`: `Nette\Forms\Controls\BaseControl::setCaption()`, `Nette\Forms\Controls\BaseControl::addError()`, `Nette\Forms\Form::addError()`, `Nette\Forms\Controls\SelectBox::setPrompt()` and the `$label`/`$caption` parameter of the `Nette\Forms\Container::add*()` factory methods (01039d16)
- **Changed** `Nette\Forms\ControlGroup`, `Nette\Forms\Helpers`, `Nette\Forms\Rule`, `Nette\Forms\Rules` and `Nette\Forms\Validator` are now `final`; affects subclasses (7dab8e3c)
- **Changed** `Nette\Forms\ControlGroup`, `Nette\Forms\Rule`, `Nette\Forms\Rules` and `Nette\Forms\Rendering\DefaultFormRenderer` no longer use `Nette\SmartObject`, so magic property access, extension methods and the exception on a mistyped member are gone (f803b8a4)
- **Changed** `Nette\Forms\Rendering\DefaultFormRenderer::renderControls()` throws a `TypeError` for an argument that is neither a container nor a control group; previously `Nette\InvalidArgumentException` with a custom message; affects code catching it (dffc07dd)
- **Changed** `Nette\Forms\Helpers::createInputList()` never self-closes the generated `<input>` tag; previously it honored `Nette\Utils\Html::$xhtml` (d164e123)
- **Changed** `{inputError}` without an argument now fails at compile time; previously it triggered `E_USER_DEPRECATED` and used the last rendered input (deprecated since 3.1.10) (d0e05052)

## 3.1.15

- **Renamed** `Nette\Forms\Rendering\DataClassGenerator` → `Nette\Forms\Blueprint`: the new class is used through `Nette\Forms\Blueprint::dataClass()`, which prints the generated class and exits, or `generateDataClass()`, which no longer supports the `$useSmartObject` property; the old name is silently deprecated (0d30e585)
- **Renamed** `Nette\Forms\Rendering\LatteRenderer` → `Nette\Forms\Blueprint`: the new class is used through `Nette\Forms\Blueprint::latte()`, which prints the generated template and exits, or `generateLatte()`; the old name is silently deprecated (0d30e585)
- **Changed** `Nette\Forms\Controls\DateTimeControl::getValue()` returns a date part of `0001-01-01` for a time-only control; previously `0000-01-01` (3042a0ac)
- **Changed** an empty `<label n:name=...></label>` or `<button n:name=...></button>` of a checkbox or radio list item now renders the item caption; previously it rendered empty (c1794e6f)

## 3.1.14

- **Changed** the `Nette\Forms\Form::Image` rule and the `accept` attribute now derive the allowed MIME types from the image formats the server supports, instead of a fixed list; the set therefore differs per installation, and the client-side validator uses the list supplied by the server (54184c64)

## 3.1.12

- **Changed** the rendered form no longer contains the hidden `<input type=IEbug>` workaround; affects code and tests matching the rendered output (87f28816)
- **Changed** `Nette.showModal()` displays the validation message in a native `<dialog>` in every browser that supports it; previously most browsers fell back to `alert()` (5f12c99d)

## 3.1.11

- **Renamed** `Nette\Forms\Form::BLANK` → `Nette\Forms\Form::Blank`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::COUNT` → `Nette\Forms\Form::Count`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::DATA_FILE` → `Nette\Forms\Form::DataFile`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::DATA_KEYS` → `Nette\Forms\Form::DataKeys`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::DATA_LINE` → `Nette\Forms\Form::DataLine`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::DATA_TEXT` → `Nette\Forms\Form::DataText`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::EMAIL` → `Nette\Forms\Form::Email`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::EQUAL` → `Nette\Forms\Form::Equal`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::FILLED` → `Nette\Forms\Form::Filled`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::FLOAT` → `Nette\Forms\Form::Float`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::GET` → `Nette\Forms\Form::Get`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::IMAGE` → `Nette\Forms\Form::Image`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::INTEGER` → `Nette\Forms\Form::Integer`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::IS_IN` → `Nette\Forms\Form::IsIn`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::IS_NOT_IN` → `Nette\Forms\Form::IsNotIn`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::LENGTH` → `Nette\Forms\Form::Length`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::MAX` → `Nette\Forms\Form::Max`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::MAX_FILE_SIZE` → `Nette\Forms\Form::MaxFileSize`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::MAX_LENGTH` → `Nette\Forms\Form::MaxLength`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::MAX_POST_SIZE` → `Nette\Forms\Form::MaxPostSize`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::MIME_TYPE` → `Nette\Forms\Form::MimeType`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::MIN` → `Nette\Forms\Form::Min`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::MIN_LENGTH` → `Nette\Forms\Form::MinLength`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::NOT_EQUAL` → `Nette\Forms\Form::NotEqual`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::NUMERIC` → `Nette\Forms\Form::Numeric`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::PATTERN` → `Nette\Forms\Form::Pattern`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::PATTERN_ICASE` → `Nette\Forms\Form::PatternInsensitive`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::POST` → `Nette\Forms\Form::Post`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::PROTECTOR_ID` → `Nette\Forms\Form::ProtectorId`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::RANGE` → `Nette\Forms\Form::Range`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::REQUIRED` → `Nette\Forms\Form::Required`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::SUBMITTED` → `Nette\Forms\Form::Submitted`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::TRACKER_ID` → `Nette\Forms\Form::TrackerId`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Form::VALID` → `Nette\Forms\Form::Valid`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Controls\CsrfProtection::PROTECTION` → `Nette\Forms\Controls\CsrfProtection::Protection`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Controls\SelectBox::VALID` → `Nette\Forms\Controls\SelectBox::Valid`; the old name still works as an alias (6a2b70e5)
- **Renamed** `Nette\Forms\Controls\UploadControl::VALID` → `Nette\Forms\Controls\UploadControl::Valid`; the old name still works as an alias (6a2b70e5)
- **Changed** a control created by `Nette\Forms\Container::addMultiUpload()` now gets an automatic rule limiting the number of uploaded files to the `max_file_uploads` directive; previously no such rule was added (2974666c)
- **Changed** the caption of `<button n:name>` is escaped context-aware by Latte; previously with `htmlspecialchars()`, so an `Nette\Utils\Html` caption is no longer double-escaped (4864154e)

## 3.1.10

- **Renamed** `Nette\Forms\Container::getUnsafeValues()` → `Nette\Forms\Container::getUntrustedValues()`; the old name still works as an alias (fb2df3bf)
- **Deprecated** `{inputError}` without an argument: pass the input, as in `{inputError $name}`; omitting it triggers `E_USER_DEPRECATED` (d7e82505)
- **Changed** `{input}`, `{label}`, `{inputError}` and `n:name` used outside `{form}` now throw a `LogicException` reporting the missing form declaration; previously the failure was reported obscurely (0a68eae5)

## 3.1.8

- **Deprecated** the second `$default` argument of `Nette\Forms\Controls\BaseControl::getOption()`: write `getOption($key) ?? $default`; the argument is no longer declared, so it cannot be passed by name, and its use is silently deprecated (a8a4935d)
- **Deprecated** the second `$default` argument of `Nette\Forms\ControlGroup::getOption()`: write `getOption($key) ?? $default`; the argument is no longer declared, so it cannot be passed by name, and its use is silently deprecated (a8a4935d)
- **Changed** `Nette\Forms\Form::initialize()` sets the CSRF cookie with the `Secure` flag when the request is over HTTPS; previously the flag was never set (23a76c53)

## 3.1.5

- **Removed** `Nette\Forms\FormFactory`: the class introduced in 3.1.2 is gone together with its DI service, create the form with `new Nette\Forms\Form` (6fc73508)

## 3.1.4

- **Changed** submitting a form that has no `onSuccess`, `onSubmit` or `onClick` handler triggers `E_USER_WARNING`; previously the submission passed silently (63d89dff)
- **Changed** `Nette\Forms\Controls\Button::getControl()` renders the caption as element content when the caption is an `Nette\Utils\Html` object or the element is a `<button>`; previously it always went into the `value` attribute (8314a914)

## 3.1.2

- **Changed** `Nette\Forms\Form::fireEvents()` treats only `Nette\Forms\Controls\SubmitButton` and its subclasses as the submitter; a class that merely implements `Nette\Forms\SubmitterControl` no longer has its `onClick` and `onInvalidClick` invoked (2b7f5687)
- **Changed** standalone forms share one lazily created `Nette\Http\Request` set up by `Nette\Forms\Form::initialize()`, which throws `Nette\InvalidStateException` when headers were already sent; previously each form silently created its own request (54858a9e)
- **Changed** `Nette\Forms\Controls\TextBase::addRule()` and `Nette\Forms\Controls\TextInput::addRule()` store a rule added after a rule whose validator cannot be exported to JavaScript again, but without deriving the input `type` and `maxlength` from it; since 3.1.0 such a rule was silently dropped (f49fa21b)

## 3.1.0

- **Removed** `Nette\Forms\Controls\BaseControl::enableAutoOptionalMode()` (deprecated since 3.0.0): controls are optional automatically (f2aff8f1)
- **Removed** the third `$multiple` argument of `Nette\Forms\Container::addUpload()` (deprecated since 3.0.0): use `Nette\Forms\Container::addMultiUpload()`; the argument is now silently ignored (f2aff8f1)
- **Renamed** the template variable `$_form` available inside `{form}` and `{formContainer}` → `$form`; the old name was removed (8cab4723)
- **Renamed** `Nette\Forms\IControl` → `Nette\Forms\Control`; the old name still works as an alias (c71aba25)
- **Renamed** `Nette\Forms\IFormRenderer` → `Nette\Forms\FormRenderer`; the old name still works as an alias (c71aba25)
- **Renamed** `Nette\Forms\ISubmitterControl` → `Nette\Forms\SubmitterControl`; the old name still works as an alias (c71aba25)
- **Renamed** `Nette\Forms\Container::addImage()` → `Nette\Forms\Container::addImageButton()`; the old name forwards to the new one and is silently deprecated (651bbcc8)
- **Renamed** `Nette\Forms\Controls\Checkbox::getSeparatorPrototype()` → `Nette\Forms\Controls\Checkbox::getContainerPrototype()`; the old name is silently deprecated (53785022)
- **Changed** a POST form is considered submitted only when `Nette\Http\Request::isSameSite()` is true; a genuine cross-origin POST is ignored unless `Nette\Forms\Form::allowCrossOrigin()` is called (46c7a26f)
- **Changed** `Nette\Forms\Form::getValues()` returns only the controls within the validation scope of the button that submitted the form; previously all controls, and the ones outside the scope are no longer validated either (294d807e)
- **Changed** the first parameter of a `Nette\Forms\Form::$onSuccess`, `Nette\Forms\Container::$onValidate` or `Nette\Forms\Controls\SubmitButton::$onClick` handler is matched by its type hint: a handler whose first parameter does not accept the form or the button receives the values instead; previously the form or button was always passed first (2fce9b91)
- **Changed** a `Nette\Forms\Form::$onValidate` handler declaring a second parameter receives `null` when the form is not valid; previously the values were passed regardless (b67ff3fc)
- **Changed** the `Nette\Forms\Form::URL` rule prepends `https://` to a value without a scheme, both on the server and in `netteForms.js`; previously `http://` (484793a3)
- **Changed** `Nette\Forms\Validator::validateEqual()` returns `false` when the control value is an empty array; previously `true` (8ea1e007)
- **Changed** `Nette\Forms\Controls\TextBase::addRule()` and `Nette\Forms\Controls\TextInput::addRule()` silently ignore a rule added after a rule whose validator cannot be exported to JavaScript; previously every rule was added (1592cf64)

## 3.0.7

- **Signature** `Nette.toggle()` takes a fourth `event` argument; affects code overriding it (f59c35e5)
- **Changed** the `srcElement` argument of `Nette.toggle()` is the control whose rule declared the toggle; previously the element that triggered the change event, and `undefined` during page load; affects code overriding it (b70ffb5c)

## 3.0.5

- **Changed** `Nette\Forms\Controls\CsrfProtection` starts the session as soon as it is attached to a presenter or a standalone form, so the session cookie is emitted earlier; previously the session was started while the token was generated during rendering (ef8ae0ed)
- **Changed** `Nette\Forms\Controls\CsrfProtection::getToken()` throws `Nette\InvalidStateException` when called before the control is attached; previously a session was created lazily (ef8ae0ed)
- **Changed** `Nette\Forms\Rules::getIterator()` yields rules with the `Nette\Forms\Form::BLANK` condition on the same control before the required rule; the evaluation order of such rules therefore differs (519ad859)
- **Changed** `Nette\Forms\Rules::elseCondition()` swaps `Nette\Forms\Form::FILLED` and `Nette\Forms\Form::BLANK` for that pair of conditions; previously it only negated the rule, which gave a wrong result (1b060570)
- **Changed** the required rule is tagged with the validator `Nette\Forms\Form::FILLED`; previously `Nette\Forms\Form::REQUIRED`; affects code inspecting `Nette\Forms\Rule::$validator` to detect it, such as a custom renderer (206edb73)
- **Changed** `Nette\Forms\Rules::validate()` respects an explicitly passed `false` and its `$emptyOptional` default changed from `false` to `null`; previously an explicit `false` was overwritten by the computed value (d77b7226)
- **Changed** `Nette.initForm()` does nothing for a form that has no field with validation rules; previously it always set `noValidate` and bound the submit handler (2b70252c)

## 3.0.4

- **Signature** the return type `void` was removed from `Nette\Forms\Controls\Button::getLabel()`, `Nette\Forms\Controls\Checkbox::getLabel()` and `Nette\Forms\Controls\HiddenField::getLabel()`; affects subclasses overriding them (d2d89b01)
- **Signature** `Nette\Forms\Rendering\DefaultFormRenderer::getWrapper()` is now `public`; previously `protected` (53b85ac9)
- **Changed** `Nette\Forms\Container::getValues('array')` returns a nested container that has its own mapped type as an instance of that type; previously every nested container was forced to an array (56b6ed7e)
- **Changed** `Nette\Forms\Rendering\DefaultFormRenderer::renderControl()` applies the control's `class` and `id` options to the control container when the pair container is empty; previously they were dropped (149a032c)
- **Changed** `Nette\Forms\Validator::validateInteger()` returns `false` for an integer too big for a native int; previously it returned `true` and left the value a numeric string (bfccde86)

## 3.0.3

- **Changed** `Nette\Forms\Controls\HiddenField::setValue()` keeps the value's original type, so `getValue()` can return an int or bool; previously the value was cast to a string when set, casting now happens only while rendering (ba33f201)
- **Changed** `Nette\Forms\Controls\UploadControl` adds a `Nette\Forms\Form::MAX_FILE_SIZE` rule taken from the `upload_max_filesize` directive on construction; adding the rule explicitly replaces the automatic one (04eebfaf)
- **Changed** adding a `Nette\Forms\Form::MAX_FILE_SIZE` rule with a value above the `upload_max_filesize` directive triggers `E_USER_WARNING` (ec59537f)

## 3.0.2

- **Removed** `Nette.toggles`: the collection of registered toggles became private (2b4dd0e9)
- **Changed** `Nette\Forms\Form::__toString()` rethrows an exception raised while rendering on PHP 7.4 and later; previously it was converted to `E_USER_ERROR` (12a72c90)

## 3.0.1

- **Changed** `Nette\Forms\Controls\BaseControl::translate()` skips translation of any value implementing `Nette\Utils\IHtmlString`; previously only of `Nette\Utils\Html` (9982f49b)
- **Changed** `Nette.toggle()` treats an argument that is a valid id containing a dot, colon or hyphen as an id again and prefixes it with `#`; since 3.0.0 it was passed through as a CSS selector (dbf9ff5f)

## 3.0.0

- **Removed** `Nette\Forms\Form::validateParent()`: the protected hook is gone together with `attached()`; affects subclasses overriding it (518dc1c1)
- **Removed** `Nette.inArray()`: use `Array.prototype.indexOf()` (7c8f3528)
- **Removed** `Nette.isArray()`: use `Array.isArray()` (7c8f3528)
- **Removed** `Nette.addEvent()`: call `element.addEventListener()` directly (883d3711)
- **Removed** `Nette.parseJSON()`: call `JSON.parse()` directly; the legacy eval-based format of Nette 2.0 is no longer supported (56138934)
- **Renamed** `Nette\Forms\Form::PROTECTION` → `Nette\Forms\Controls\CsrfProtection::PROTECTION`; the old name was removed (c8558895)
- **Renamed** `Nette\Forms\Rules::$defaultMessages` → `Nette\Forms\Validator::$messages`; the old name was removed (c8558895)
- **Renamed** `Nette\Bridges\FormsLatte\FormMacros::renderFormBegin()` → `Nette\Bridges\FormsLatte\Runtime::renderFormBegin()`; the old name was removed (c8558895)
- **Renamed** `Nette\Bridges\FormsLatte\FormMacros::renderFormEnd()` → `Nette\Bridges\FormsLatte\Runtime::renderFormEnd()`; the old name was removed (c8558895)
- **Renamed** `Nette\Forms\Controls\BaseControl::setAttribute()` → `Nette\Forms\Controls\BaseControl::setHtmlAttribute()`; the old name is silently deprecated (a77f75db)
- **Renamed** `Nette\Forms\Controls\TextInput::setType()` → `Nette\Forms\Controls\TextInput::setHtmlType()`; the old name is silently deprecated (a77f75db)
- **Replaced** `Nette\Forms\Controls\BaseControl::attached()` with `Nette\ComponentModel\Component::monitor()`: the protected hook was removed, pass the logic as the callback of `$this->monitor(Nette\Forms\Form::class, function ($form) {...})` in the constructor; overriding `attached()` has no effect in any control (518dc1c1)
- **Replaced** `Nette\Forms\Form::attached()` with `Nette\ComponentModel\Component::monitor()`: the protected hook was removed, pass the logic as the callback of `$this->monitor(self::class, function () {...})` in the constructor (518dc1c1)
- **Replaced** `Nette\Forms\Controls\BaseControl::$caption` with `Nette\Forms\Controls\BaseControl::setCaption()` and `Nette\Forms\Controls\BaseControl::getCaption()`: the property is now private, while reading `$control->caption` keeps working through the magic accessor (4cba408b)
- **Replaced** `Nette\Forms\Controls\ChoiceControl::$checkAllowedValues` with `Nette\Forms\Controls\ChoiceControl::checkDefaultValue()`: the public property was removed, call `checkDefaultValue(false)` instead of assigning `false` (6bc3a294)
- **Replaced** `Nette\Forms\Controls\MultiChoiceControl::$checkAllowedValues` with `Nette\Forms\Controls\MultiChoiceControl::checkDefaultValue()`: the public property was removed, call `checkDefaultValue(false)` instead of assigning `false` (6bc3a294)
- **Replaced** registering an extension method for a container through `Nette\Utils\ObjectMixin` with `Nette\Forms\Container::extensionMethod()`: the fallback was removed, so a method registered the old way is no longer found (48fd2c10)
- **Replaced** registering an extension method for a control through `Nette\Utils\ObjectMixin` with `Nette\Forms\Controls\BaseControl::extensionMethod()`: the fallback was removed, so a method registered the old way is no longer found (48fd2c10)
- **Deprecated** `Nette\Forms\Controls\BaseControl::enableAutoOptionalMode()`: controls are optional automatically; calling it triggers `E_USER_DEPRECATED` (5a6ec496)
- **Deprecated** the third `$multiple` argument of `Nette\Forms\Container::addUpload()`: use `Nette\Forms\Container::addMultiUpload()`; passing it triggers `E_USER_DEPRECATED` (7c658936)
- **Signature** native scalar and return types were added throughout the package, so a value of another type now throws a `TypeError` and a subclass or interface implementation must repeat the type: `Nette\Forms\IControl::getErrors()` (`array`), `Nette\Forms\IControl::isOmitted()` (`bool`), `Nette\Forms\IControl::validate()` (`void`), `Nette\Forms\IFormRenderer::render()` (`string`), `Nette\Forms\ISubmitterControl::getValidationScope()` (`?array`), `Nette\Forms\Controls\BaseControl::isFilled()` (`bool`), `Nette\Forms\Controls\BaseControl::loadHttpData()` (`void`), `Nette\Forms\Controls\BaseControl::validate()` (`void`), `Nette\Forms\Controls\BaseControl::getControlPart()`, `Nette\Forms\Controls\BaseControl::getLabelPart()`, `Nette\Forms\Controls\BaseControl::getHtmlName()`, `Nette\Forms\Controls\BaseControl::getRules()`, `Nette\Forms\Controls\BaseControl::getForm()`, `Nette\Forms\Controls\TextBase::getControl()` and all `render*()` methods of `Nette\Forms\Rendering\DefaultFormRenderer` (8120b73b, 310b72fb)
- **Signature** `Nette\Forms\Controls\BaseControl::setTranslator()`: `$translator` is typed `?Nette\Localization\ITranslator` and lost its default, so calling without an argument fails; pass `null` (8120b73b)
- **Signature** `Nette\Forms\Form::setTranslator()`: `$translator` is typed `?Nette\Localization\ITranslator` and lost its default, so calling without an argument fails; pass `null` (8120b73b)
- **Signature** `Nette\Forms\Form::setRenderer()`: `$renderer` is typed `?Nette\Forms\IFormRenderer` and lost its default, so calling without an argument fails; pass `null` (8120b73b)
- **Signature** `Nette\Forms\Controls\SubmitButton::setValidationScope()`: `$scope` is typed `?iterable` and lost its default; pass `[]` instead of `false` for an empty scope and `null` instead of `true`, both now throw a `TypeError` (ece21be1, 97b8877c)
- **Signature** `Nette\Forms\Validator::validateBlank()`: `$control` is typed `Nette\Forms\Controls\BaseControl`; previously `Nette\Forms\IControl`, so an implementation outside that class hierarchy can no longer be passed (e8be4068)
- **Signature** `Nette\Forms\Validator::validateFilled()`: `$control` is typed `Nette\Forms\Controls\BaseControl`; previously `Nette\Forms\IControl`, so an implementation outside that class hierarchy can no longer be passed (e8be4068)
- **Signature** `Nette\Forms\Validator::validatePattern()`: `$pattern` is typed `string`; previously any value was interpolated into the regular expression (2b94332b)
- **Signature** `Nette\Forms\Controls\BaseControl::getHttpData()`: `$htmlTail` is typed `?string`; affects subclasses overriding or calling this protected method (310b72fb)
- **Signature** `Nette\Forms\Container::extensionMethod()`: `$name` is typed `string` and `$callback` is required; previously it defaulted to `null` (a8c2e656, 310b72fb)
- **Signature** `Nette\Forms\Controls\BaseControl::extensionMethod()`: `$name` is typed `string` and `$callback` is required; previously it defaulted to `null` (a8c2e656, 310b72fb)
- **Signature** `Nette\Forms\Container::addComponent()`: `$name` is typed `?string` and `$insertBefore` `?string`; passing another type, such as an int, now throws a `TypeError` (b80992e6)
- **Signature** `Nette\Forms\Container::getValues()`: the parameter is `$returnType`, which takes a class name or `'array'` to map the values onto; previously the bool `$asArray`, which still works (574b97f9)
- **Signature** `Nette\Forms\Container::setValues()`: the first parameter is named `$data` and also accepts a plain object; previously `iterable $values`, so a named argument must be renamed (032cf954)
- **Signature** `Nette\Forms\Container::setDefaults()`: the first parameter is named `$data` and also accepts a plain object; previously `iterable $values`, so a named argument must be renamed (032cf954)
- **Signature** `Nette\Forms\Controls\BaseControl::translate()`: the extra arguments are the variadic `$parameters` forwarded to the translator; previously the single `$count` (e7d4235d)
- **Changed** `Nette\Forms\Form::NUMERIC` is no longer an alias of `Nette\Forms\Form::INTEGER`: the rule only checks that the value consists of digits and does not cast it to an int (65793faa)
- **Changed** `Nette\Forms\Controls\BaseControl::addError()` translates the message through the translator; previously it did not; call `addError($message, false)` to keep the previous behavior (18880295)
- **Changed** `Nette\Forms\Form::addError()` translates the message through the translator; previously it did not; call `addError($message, false)` to keep the previous behavior (18880295)
- **Changed** `Nette\Forms\Controls\BaseControl::getHtmlId()` throws when the control is not attached to a form; previously it returned an id computed without the form (57544216)
- **Changed** `Nette\Forms\Controls\BaseControl::getHtmlId()` prefixes the generated id with the form name for a named standalone form, as in `frm-myform-name`; previously the name was not part of the id (9cb04d2b)
- **Changed** `Nette\Forms\Controls\BaseControl::getLabel()` translates the label only through the form's translator; previously through the control's own translation logic, which can change the rendered text (782b3199)
- **Changed** `Nette\Forms\Controls\CheckboxList::getItemLabelPrototype()` is the only source of item label customization; previously item labels fell back to `getLabelPrototype()` when it was not used (085804c3)
- **Changed** `Nette\Forms\Rendering\DefaultFormRenderer::renderErrors()` passes error messages through the translator, taken from the control when it is a `Nette\Forms\Controls\BaseControl` and from the form otherwise; previously they were rendered untranslated (7c4e4ca2, 32d2cf07)
- **Changed** a `Nette\Forms\Controls\SubmitButton::$onClick` handler declaring a second parameter also receives the parsed values, like an `onSuccess` handler; previously only the button was passed (4e835599)
- **Changed** the remaining `Nette\Forms\Controls\SubmitButton::$onClick` handlers are not invoked once a handler makes the form invalid; previously all of them ran (d1eb1b2b)
- **Changed** the type hint of the second parameter of an `onValidate`, `onSuccess`, `onSubmit` or `onError` handler selects the type the values are mapped onto; previously only an `array` type hint was distinguished and anything else yielded `Nette\Utils\ArrayHash` (574b97f9)
- **Changed** `Nette\Forms\Helpers::createSelectBox()` marks only the first option with a matching value as selected; previously every one of them could get the `selected` attribute (f088f458)
- **Changed** `Nette\Forms\Controls\SelectBox` adds the automatic check of the selected value as a rule conditioned on the control being filled, applied only when there is no prompt, items exist and the size is below 2; previously it ran whenever the value was empty (2210656d)
- **Changed** the `Nette\Forms\Form::PATTERN` rule on an upload control matches the uploaded file's name; previously the raw control value (9ea7e2ee)
- **Changed** the `%label` placeholder of `Nette\Forms\Validator::formatMessage()` strips a trailing colon from the caption; error messages built from a caption ending with a colon therefore render differently (74b8df9d)
- **Changed** `Nette.toggle()` shows and hides elements with the `hidden` attribute; previously with an inline `style.display`, so CSS overriding `[hidden]` may need adjusting (c5c6141a)
- **Changed** the first argument of `Nette.toggle()` is treated as a CSS selector and prefixed with `#` only when it consists of word characters; an id containing a hyphen must be passed with an explicit `#`, and all matching elements are toggled (d8627173)
