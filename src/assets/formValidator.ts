import { FormElement, FormElementValue, FormError, Rule, ToggleState, Validator } from './types';
import { Validators } from './validators';

export class FormValidator {
	formErrors: FormError[] = [];
	validators = new Validators as any as Record<string, Validator>;
	#preventFiltering: Record<string, boolean> = {};
	#formToggles: Record<string, ToggleState> = {};
	#toggleListeners: WeakMap<FormElement, null> = new WeakMap;


	#getFormElement(form: HTMLFormElement, name: string): FormElement | null {
		let res = form.elements.namedItem(name);
		return (res instanceof RadioNodeList ? res[0] : res) as FormElement | null;
	}


	#expandRadioElement<Element extends FormElement>(elem: Element): Element[] {
		let res = elem.form.elements.namedItem(elem.name);
		return (res instanceof RadioNodeList ? Array.from(res) : [res]) as Element[];
	}


	/**
	 * Function to execute when the DOM is fully loaded.
	 */
	#onDocumentReady(callback: () => void): void {
		if (document.readyState !== 'loading') {
			callback.call(this);
		} else {
			document.addEventListener('DOMContentLoaded', callback);
		}
	}


	/**
	 * Returns the value of form element.
	 */
	getValue(elem: FormElement | RadioNodeList): FormElementValue {
		if (elem instanceof HTMLInputElement) {
			if (elem.type === 'radio') {
				return this.#expandRadioElement(elem)
					.find((input) => input.checked)
					?.value ?? null;

			} else if (elem.type === 'file') {
				return elem.files;

			} else if (elem.type === 'checkbox') {
				return elem.name.endsWith('[]') // checkbox list
					? this.#expandRadioElement(elem)
						.filter((input) => input.checked)
						.map((input) => input.value)
					: elem.checked;

			} else {
				return elem.value.trim();
			}

		} else if (elem instanceof HTMLSelectElement) {
			return elem.multiple
				? Array.from(elem.selectedOptions, (option) => option.value)
				: elem.selectedOptions[0]?.value ?? null;

		} else if (elem instanceof HTMLTextAreaElement) {
			return elem.value;

		} else if (elem instanceof RadioNodeList) {
			return this.getValue(elem[0] as FormElement);

		} else {
			return null;
		}
	}


	/**
	 * Returns the effective value of form element.
	 */
	getEffectiveValue(elem: FormElement, filter: boolean = false): FormElementValue {
		let val = this.getValue(elem);
		if (val === elem.getAttribute('data-nette-empty-value')) {
			val = '';
		}
		if (filter && this.#preventFiltering[elem.name] === undefined) {
			this.#preventFiltering[elem.name] = true;
			let ref = { value: val };
			this.validateControl(elem, undefined, true, ref);
			val = ref.value;
			delete this.#preventFiltering[elem.name];
		}
		return val;
	}


	/**
	 * Validates form element against given rules.
	 */
	validateControl(
		elem: FormElement,
		rules?: Rule[],
		onlyCheck: boolean = false,
		value?: { value: FormElementValue },
		emptyOptional?: boolean,
	): boolean {
		rules ??= JSON.parse(elem.getAttribute('data-nette-rules') ?? '[]') as Rule[];
		value ??= { value: this.getEffectiveValue(elem) };
		emptyOptional ??= !this.validateRule(elem, ':filled', null, value);

		for (let rule of rules) {
			let op = rule.op.match(/(~)?([^?]+)/)!,
				curElem = rule.control ? this.#getFormElement(elem.form, rule.control) : elem;

			rule.neg = !!op[1];
			rule.op = op[2]!;
			rule.condition = !!rule.rules;

			if (!curElem) {
				continue;
			} else if (emptyOptional && !rule.condition && rule.op !== ':filled') {
				continue;
			}

			let success = this.validateRule(curElem, rule.op, rule.arg, elem === curElem ? value : undefined);
			if (success === null) {
				continue;
			} else if (rule.neg) {
				success = !success;
			}

			if (rule.condition && success) {
				if (!this.validateControl(elem, rule.rules, onlyCheck, value, rule.op === ':blank' ? false : emptyOptional)) {
					return false;
				}
			} else if (!rule.condition && !success) {
				if (this.isDisabled(curElem)) {
					continue;
				}
				if (!onlyCheck) {
					let arr = Array.isArray(rule.arg) ? rule.arg : [rule.arg],
						message = rule.msg.replace(
							/%(value|\d+)/g,
							(foo, m) => this.getValue(m === 'value' ? curElem : elem.form.elements.namedItem(arr[m].control) as FormElement | RadioNodeList) as string,
						);
					this.addError(curElem, message);
				}
				return false;
			}
		}

		return true;
	}


	/**
	 * Validates whole form.
	 */
	validateForm(sender: HTMLFormElement | FormElement, onlyCheck: boolean = false): boolean {
		let form: HTMLFormElement = sender.form ?? sender,
			scope;

		this.formErrors = [];

		if (sender.getAttribute('formnovalidate') !== null) {
			let scopeArr = JSON.parse(sender.getAttribute('data-nette-validation-scope') ?? '[]') as string[];
			if (scopeArr.length) {
				scope = new RegExp('^(' + scopeArr.join('-|') + '-)');
			} else {
				this.showFormErrors(form, []);
				return true;
			}
		}

		for (let elem of form.elements as any as FormElement[]) {
			if (elem.willValidate && elem.validity.badInput) {
				elem.reportValidity();
				return false;
			}
		}

		for (let elem of form.elements as any as FormElement[]) {
			if (elem.getAttribute('data-nette-rules')
				&& (!scope || elem.name.replace(/]\[|\[|]|$/g, '-').match(scope))
				&& !this.isDisabled(elem)
				&& !this.validateControl(elem, undefined, onlyCheck)
				&& !this.formErrors.length
			) {
				return false;
			}
		}

		let success = !this.formErrors.length;
		this.showFormErrors(form, this.formErrors);
		return success;
	}


	/**
	 * Check if input is disabled.
	 */
	isDisabled(elem: FormElement): boolean {
		if (elem.type === 'radio') {
			return this.#expandRadioElement(elem)
				.every((input) => input.disabled);
		}
		return elem.disabled;
	}


	/**
	 * Adds error message to the queue.
	 */
	addError(elem: FormElement, message: string): void {
		this.formErrors.push({
			element: elem,
			message: message,
		});
	}


	/**
	 * Display error messages.
	 */
	showFormErrors(form: HTMLFormElement, errors: FormError[]): void {
		let messages: string[] = [],
			focusElem: FormElement;

		for (let error of errors) {
			if (messages.indexOf(error.message) < 0) {
				messages.push(error.message);
				focusElem ??= error.element;
			}
		}

		if (messages.length) {
			this.showModal(messages.join('\n'), () => {
				focusElem?.focus();
			});
		}
	}


	/**
	 * Display modal window.
	 */
	showModal(message: string, onclose: () => void): void {
		let dialog = document.createElement('dialog');

		if (!dialog.showModal) {
			alert(message);
			onclose();
			return;
		}

		let style = document.createElement('style');
		style.innerText = '.netteFormsModal { text-align: center; margin: auto; border: 2px solid black; padding: 1rem } .netteFormsModal button { padding: .1em 2em }';

		let button = document.createElement('button');
		button.innerText = 'OK';
		button.onclick = () => {
			dialog.remove();
			onclose();
		};

		dialog.setAttribute('class', 'netteFormsModal');
		dialog.innerText = message + '\n\n';
		dialog.append(style, button);
		document.body.append(dialog);
		dialog.showModal();
	}


	/**
	 * Validates single rule.
	 */
	validateRule(elem: FormElement, op: string, arg: unknown, value?: { value: FormElementValue }): boolean | null {
		if (elem.validity.badInput) {
			return op === ':filled';
		}

		value ??= { value: this.getEffectiveValue(elem, true) };

		let method = op.charAt(0) === ':' ? op.substring(1) : op;
		method = method.replace('::', '_').replaceAll('\\', '');

		let args = Array.isArray(arg) ? arg : [arg];
		args = args.map((arg) => {
			if (arg?.control) {
				let control = this.#getFormElement(elem.form, arg.control)!;
				return control === elem ? value.value : this.getEffectiveValue(control, true);
			}
			return arg;
		});

		if (method === 'valid') {
			args[0] = this; // todo
		}

		return this.validators[method]
			? this.validators[method]!(elem, Array.isArray(arg) ? args : args[0], value.value, value)
			: null;
	}


	/**
	 * Process all toggles in form.
	 */
	toggleForm(form: HTMLFormElement, event?: Event): void {
		this.#formToggles = {};
		for (let elem of Array.from(form.elements) as FormElement[]) {
			if (elem.getAttribute('data-nette-rules')) {
				this.toggleControl(elem, undefined, null, !event);
			}
		}

		for (let i in this.#formToggles) {
			this.toggle(i, this.#formToggles[i]!.state, this.#formToggles[i]!.elem, event);
		}
	}


	/**
	 * Process toggles on form element.
	 */
	toggleControl(
		elem: FormElement,
		rules?: Rule[],
		success: boolean | null = null,
		firsttime: boolean = false,
		value?: { value: FormElementValue },
		emptyOptional?: boolean,
	): boolean {
		rules ??= JSON.parse(elem.getAttribute('data-nette-rules') ?? '[]') as Rule[];
		value ??= { value: this.getEffectiveValue(elem) };
		emptyOptional ??= !this.validateRule(elem, ':filled', null, value);

		let has = false,
			curSuccess: boolean | null;

		for (let rule of rules) {
			let op = rule.op.match(/(~)?([^?]+)/)!,
				curElem = rule.control ? this.#getFormElement(elem.form, rule.control) : elem;

			rule.neg = !!op[1];
			rule.op = op[2]!;
			rule.condition = !!rule.rules;

			if (!curElem) {
				continue;
			} else if (emptyOptional && !rule.condition && rule.op !== ':filled') {
				continue;
			}

			curSuccess = success;
			if (success !== false) {
				curSuccess = this.validateRule(curElem, rule.op, rule.arg, elem === curElem ? value : undefined);
				if (curSuccess === null) {
					continue;

				} else if (rule.neg) {
					curSuccess = !curSuccess;
				}
				if (!rule.condition) {
					success = curSuccess;
				}
			}

			if ((rule.condition && this.toggleControl(elem, rule.rules, curSuccess, firsttime, value, rule.op === ':blank' ? false : emptyOptional)) || rule.toggle) {
				has = true;
				if (firsttime) {
					this.#expandRadioElement(curElem)
						.filter((el) => !this.#toggleListeners.has(el))
						.forEach((el) => {
							el.addEventListener('change', (e) => this.toggleForm(elem.form, e));
							this.#toggleListeners.set(el, null);
						});
				}
				for (let id in rule.toggle ?? {}) {
					this.#formToggles[id] ??= { elem: elem, state: false };
					this.#formToggles[id].state ||= rule.toggle![id] ? !!curSuccess : !curSuccess;
				}
			}
		}
		return has;
	}


	/**
	 * Displays or hides HTML element.
	 */
	toggle(selector: string, visible: boolean, srcElement: FormElement, event?: Event): void { // eslint-disable-line @typescript-eslint/no-unused-vars
		if (/^\w[\w.:-]*$/.test(selector)) { // id
			selector = '#' + selector;
		}
		Array.from(document.querySelectorAll<HTMLElement>(selector))
			.forEach((elem) => elem.hidden = !visible);
	}


	/**
	 * Compact checkboxes
	 */
	compactCheckboxes(form: HTMLFormElement, formData: FormData): void {
		let values: Record<string, string[]> = {};

		for (let elem of form.elements) {
			if (elem instanceof HTMLInputElement && elem.type === 'checkbox' && elem.name.endsWith('[]') && elem.checked && !elem.disabled) {
				formData.delete(elem.name);
				values[elem.name] ??= [];
				values[elem.name]!.push(elem.value);
			}
		}

		for (let name in values) {
			formData.set(name.substring(0, name.length - 2), values[name]!.join(','));
		}
	}


	/**
	 * Setup handlers.
	 */
	initForm(form: HTMLFormElement): void {
		if (form.method === 'get' && form.hasAttribute('data-nette-compact')) {
			form.addEventListener('formdata', (e) => this.compactCheckboxes(form, e.formData));
		}

		if (!Array.from(form.elements).some((elem) => elem.getAttribute('data-nette-rules'))) {
			return;
		}

		this.toggleForm(form);

		if (form.noValidate) {
			return;
		}
		form.noValidate = true;

		form.addEventListener('submit', (e) => {
			if (!this.validateForm((e.submitter || form) as HTMLFormElement | FormElement)) {
				e.stopPropagation();
				e.preventDefault();
			}
		});

		form.addEventListener('reset', () => {
			setTimeout(() => this.toggleForm(form));
		});
	}


	initOnLoad(): void {
		this.#onDocumentReady(() => {
			Array.from(document.forms)
				.forEach((form) => this.initForm(form));
		});
	}
}
