/*!
 * NetteForms - simple form validation.
 *
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
	typeof define === 'function' && define.amd ? define(factory) :
	(global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.Nette?.noInit ? (global.Nette = factory()) : (global.Nette = factory()).initOnLoad());
})(this, (function () { 'use strict';

	class Validators {
		filled(elem, arg, val) {
			return val !== '' && val !== false && val !== null
				&& (!Array.isArray(val) || val.length > 0)
				&& (!(val instanceof FileList) || val.length > 0);
		}
		blank(elem, arg, val) {
			return !this.filled(elem, arg, val);
		}
		valid(elem, arg) {
			return arg.validateControl(elem, undefined, true);
		}
		equal(elem, arg, val) {
			if (arg === undefined) {
				return null;
			}
			let toString = (val) => {
				if (typeof val === 'number' || typeof val === 'string') {
					return '' + val;
				}
				else {
					return val === true ? '1' : '';
				}
			};
			let vals = Array.isArray(val) ? val : [val];
			let args = Array.isArray(arg) ? arg : [arg];
			loop: for (let a of vals) {
				for (let b of args) {
					if (toString(a) === toString(b)) {
						continue loop;
					}
				}
				return false;
			}
			return vals.length > 0;
		}
		notEqual(elem, arg, val) {
			return arg === undefined ? null : !this.equal(elem, arg, val);
		}
		minLength(elem, arg, val) {
			val = typeof val === 'number' ? val.toString() : val;
			return val.length >= arg;
		}
		maxLength(elem, arg, val) {
			val = typeof val === 'number' ? val.toString() : val;
			return val.length <= arg;
		}
		length(elem, arg, val) {
			val = typeof val === 'number' ? val.toString() : val;
			arg = Array.isArray(arg) ? arg : [arg, arg];
			return ((arg[0] === null || val.length >= arg[0])
				&& (arg[1] === null || val.length <= arg[1]));
		}
		email(elem, arg, val) {
			return (/^("([ !#-[\]-~]|\\[ -~])+"|[-a-z0-9!#$%&'*+/=?^_`{|}~]+(\.[-a-z0-9!#$%&'*+/=?^_`{|}~]+)*)@([0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)+[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?$/i).test(val);
		}
		url(elem, arg, val, newValue) {
			if (!(/^[a-z\d+.-]+:/).test(val)) {
				val = 'https://' + val;
			}
			if ((/^https?:\/\/((([-_0-9a-z\u00C0-\u02FF\u0370-\u1EFF]+\.)*[0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)?[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|\[[0-9a-f:]{3,39}\])(:\d{1,5})?(\/\S*)?$/i).test(val)) {
				newValue.value = val;
				return true;
			}
			return false;
		}
		regexp(elem, arg, val) {
			let parts = typeof arg === 'string' ? arg.match(/^\/(.*)\/([imu]*)$/) : false;
			try {
				return parts && (new RegExp(parts[1], parts[2].replace('u', ''))).test(val);
			}
			catch {
				return null;
			}
		}
		pattern(elem, arg, val, newValue, caseInsensitive) {
			if (typeof arg !== 'string') {
				return null;
			}
			try {
				let regExp;
				try {
					regExp = new RegExp('^(?:' + arg + ')$', caseInsensitive ? 'ui' : 'u');
				}
				catch {
					regExp = new RegExp('^(?:' + arg + ')$', caseInsensitive ? 'i' : '');
				}
				return val instanceof FileList
					? Array.from(val).every((file) => regExp.test(file.name))
					: regExp.test(val);
			}
			catch {
				return null;
			}
		}
		patternCaseInsensitive(elem, arg, val) {
			return this.pattern(elem, arg, val, null, true);
		}
		numeric(elem, arg, val) {
			return (/^[0-9]+$/).test(val);
		}
		integer(elem, arg, val, newValue) {
			if ((/^-?[0-9]+$/).test(val)) {
				newValue.value = parseFloat(val);
				return true;
			}
			return false;
		}
		float(elem, arg, val, newValue) {
			val = val.replace(/ +/g, '').replace(/,/g, '.');
			if ((/^-?[0-9]*\.?[0-9]+$/).test(val)) {
				newValue.value = parseFloat(val);
				return true;
			}
			return false;
		}
		min(elem, arg, val) {
			if (Number.isFinite(arg)) {
				val = parseFloat(val);
			}
			return val >= arg;
		}
		max(elem, arg, val) {
			if (Number.isFinite(arg)) {
				val = parseFloat(val);
			}
			return val <= arg;
		}
		range(elem, arg, val) {
			if (!Array.isArray(arg)) {
				return null;
			}
			else if (elem.type === 'time' && arg[0] > arg[1]) {
				return val >= arg[0] || val <= arg[1];
			}
			return (arg[0] === null || this.min(elem, arg[0], val))
				&& (arg[1] === null || this.max(elem, arg[1], val));
		}
		submitted(elem) {
			return elem.form['nette-submittedBy'] === elem;
		}
		fileSize(elem, arg, val) {
			return Array.from(val).every((file) => file.size <= arg);
		}
		mimeType(elem, args, val) {
			let parts = [];
			args = Array.isArray(args) ? args : [args];
			args.forEach((arg) => parts.push('^' + arg.replace(/([^\w])/g, '\\$1').replace('\\*', '.*') + '$'));
			let re = new RegExp(parts.join('|'));
			return Array.from(val).every((file) => !file.type || re.test(file.type));
		}
		image(elem, arg, val) {
			return this.mimeType(elem, arg ?? ['image/gif', 'image/png', 'image/jpeg', 'image/webp'], val);
		}
		static(elem, arg) {
			return arg;
		}
	}

	class FormValidator {
		formErrors = [];
		validators = new Validators;
		#preventFiltering = {};
		#formToggles = {};
		#toggleListeners = new WeakMap;
		#getFormElement(form, name) {
			let res = form.elements.namedItem(name);
			return (res instanceof RadioNodeList ? res[0] : res);
		}
		#expandRadioElement(elem) {
			let res = elem.form.elements.namedItem(elem.name);
			return (res instanceof RadioNodeList ? Array.from(res) : [res]);
		}
		/**
		 * Function to execute when the DOM is fully loaded.
		 */
		#onDocumentReady(callback) {
			if (document.readyState !== 'loading') {
				callback.call(this);
			}
			else {
				document.addEventListener('DOMContentLoaded', callback);
			}
		}
		/**
		 * Returns the value of form element.
		 */
		getValue(elem) {
			if (elem instanceof HTMLInputElement) {
				if (elem.type === 'radio') {
					return this.#expandRadioElement(elem)
						.find((input) => input.checked)
						?.value ?? null;
				}
				else if (elem.type === 'file') {
					return elem.files;
				}
				else if (elem.type === 'checkbox') {
					return elem.name.endsWith('[]') // checkbox list
						? this.#expandRadioElement(elem)
							.filter((input) => input.checked)
							.map((input) => input.value)
						: elem.checked;
				}
				else {
					return elem.value.trim();
				}
			}
			else if (elem instanceof HTMLSelectElement) {
				return elem.multiple
					? Array.from(elem.selectedOptions, (option) => option.value)
					: elem.selectedOptions[0]?.value ?? null;
			}
			else if (elem instanceof HTMLTextAreaElement) {
				return elem.value;
			}
			else if (elem instanceof RadioNodeList) {
				return this.getValue(elem[0]);
			}
			else {
				return null;
			}
		}
		/**
		 * Returns the effective value of form element.
		 */
		getEffectiveValue(elem, filter = false) {
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
		validateControl(elem, rules, onlyCheck = false, value, emptyOptional) {
			rules ??= JSON.parse(elem.getAttribute('data-nette-rules') ?? '[]');
			value ??= { value: this.getEffectiveValue(elem) };
			emptyOptional ??= !this.validateRule(elem, ':filled', null, value);
			for (let rule of rules) {
				let op = rule.op.match(/(~)?([^?]+)/), curElem = rule.control ? this.#getFormElement(elem.form, rule.control) : elem;
				rule.neg = !!op[1];
				rule.op = op[2];
				rule.condition = !!rule.rules;
				if (!curElem) {
					continue;
				}
				else if (emptyOptional && !rule.condition && rule.op !== ':filled') {
					continue;
				}
				let success = this.validateRule(curElem, rule.op, rule.arg, elem === curElem ? value : undefined);
				if (success === null) {
					continue;
				}
				else if (rule.neg) {
					success = !success;
				}
				if (rule.condition && success) {
					if (!this.validateControl(elem, rule.rules, onlyCheck, value, rule.op === ':blank' ? false : emptyOptional)) {
						return false;
					}
				}
				else if (!rule.condition && !success) {
					if (this.isDisabled(curElem)) {
						continue;
					}
					if (!onlyCheck) {
						let arr = Array.isArray(rule.arg) ? rule.arg : [rule.arg], message = rule.msg.replace(/%(value|\d+)/g, (foo, m) => this.getValue(m === 'value' ? curElem : elem.form.elements.namedItem(arr[m].control)));
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
		validateForm(sender, onlyCheck = false) {
			let form = sender.form ?? sender, scope;
			this.formErrors = [];
			if (sender.getAttribute('formnovalidate') !== null) {
				let scopeArr = JSON.parse(sender.getAttribute('data-nette-validation-scope') ?? '[]');
				if (scopeArr.length) {
					scope = new RegExp('^(' + scopeArr.join('-|') + '-)');
				}
				else {
					this.showFormErrors(form, []);
					return true;
				}
			}
			for (let elem of form.elements) {
				if (elem.willValidate && elem.validity.badInput) {
					elem.reportValidity();
					return false;
				}
			}
			for (let elem of form.elements) {
				if (elem.getAttribute('data-nette-rules')
					&& (!scope || elem.name.replace(/]\[|\[|]|$/g, '-').match(scope))
					&& !this.isDisabled(elem)
					&& !this.validateControl(elem, undefined, onlyCheck)
					&& !this.formErrors.length) {
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
		isDisabled(elem) {
			if (elem.type === 'radio') {
				return this.#expandRadioElement(elem)
					.every((input) => input.disabled);
			}
			return elem.disabled;
		}
		/**
		 * Adds error message to the queue.
		 */
		addError(elem, message) {
			this.formErrors.push({
				element: elem,
				message: message,
			});
		}
		/**
		 * Display error messages.
		 */
		showFormErrors(form, errors) {
			let messages = [], focusElem;
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
		showModal(message, onclose) {
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
		validateRule(elem, op, arg, value) {
			if (elem.validity.badInput) {
				return op === ':filled';
			}
			value ??= { value: this.getEffectiveValue(elem, true) };
			let method = op.charAt(0) === ':' ? op.substring(1) : op;
			method = method.replace('::', '_').replaceAll('\\', '');
			let args = Array.isArray(arg) ? arg : [arg];
			args = args.map((arg) => {
				if (arg?.control) {
					let control = this.#getFormElement(elem.form, arg.control);
					return control === elem ? value.value : this.getEffectiveValue(control, true);
				}
				return arg;
			});
			if (method === 'valid') {
				args[0] = this; // todo
			}
			return this.validators[method]
				? this.validators[method](elem, Array.isArray(arg) ? args : args[0], value.value, value)
				: null;
		}
		/**
		 * Process all toggles in form.
		 */
		toggleForm(form, event) {
			this.#formToggles = {};
			for (let elem of Array.from(form.elements)) {
				if (elem.getAttribute('data-nette-rules')) {
					this.toggleControl(elem, undefined, null, !event);
				}
			}
			for (let i in this.#formToggles) {
				this.toggle(i, this.#formToggles[i].state, this.#formToggles[i].elem, event);
			}
		}
		/**
		 * Process toggles on form element.
		 */
		toggleControl(elem, rules, success = null, firsttime = false, value, emptyOptional) {
			rules ??= JSON.parse(elem.getAttribute('data-nette-rules') ?? '[]');
			value ??= { value: this.getEffectiveValue(elem) };
			emptyOptional ??= !this.validateRule(elem, ':filled', null, value);
			let has = false, curSuccess;
			for (let rule of rules) {
				let op = rule.op.match(/(~)?([^?]+)/), curElem = rule.control ? this.#getFormElement(elem.form, rule.control) : elem;
				rule.neg = !!op[1];
				rule.op = op[2];
				rule.condition = !!rule.rules;
				if (!curElem) {
					continue;
				}
				else if (emptyOptional && !rule.condition && rule.op !== ':filled') {
					continue;
				}
				curSuccess = success;
				if (success !== false) {
					curSuccess = this.validateRule(curElem, rule.op, rule.arg, elem === curElem ? value : undefined);
					if (curSuccess === null) {
						continue;
					}
					else if (rule.neg) {
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
						this.#formToggles[id].state ||= rule.toggle[id] ? !!curSuccess : !curSuccess;
					}
				}
			}
			return has;
		}
		/**
		 * Displays or hides HTML element.
		 */
		toggle(selector, visible, srcElement, event) {
			if (/^\w[\w.:-]*$/.test(selector)) { // id
				selector = '#' + selector;
			}
			Array.from(document.querySelectorAll(selector))
				.forEach((elem) => elem.hidden = !visible);
		}
		/**
		 * Compact checkboxes
		 */
		compactCheckboxes(form, formData) {
			let values = {};
			for (let elem of form.elements) {
				if (elem instanceof HTMLInputElement && elem.type === 'checkbox' && elem.name.endsWith('[]') && elem.checked && !elem.disabled) {
					formData.delete(elem.name);
					values[elem.name] ??= [];
					values[elem.name].push(elem.value);
				}
			}
			for (let name in values) {
				formData.set(name.substring(0, name.length - 2), values[name].join(','));
			}
		}
		/**
		 * Setup handlers.
		 */
		initForm(form) {
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
				if (!this.validateForm((e.submitter || form))) {
					e.stopImmediatePropagation();
					e.preventDefault();
				}
			});
			form.addEventListener('reset', () => {
				setTimeout(() => this.toggleForm(form));
			});
		}
		initOnLoad() {
			this.#onDocumentReady(() => {
				Array.from(document.forms)
					.forEach((form) => this.initForm(form));
			});
		}
	}

	let webalizeTable = { \u00e1: 'a', \u00e4: 'a', \u010d: 'c', \u010f: 'd', \u00e9: 'e', \u011b: 'e', \u00ed: 'i', \u013e: 'l', \u0148: 'n', \u00f3: 'o', \u00f4: 'o', \u0159: 'r', \u0161: 's', \u0165: 't', \u00fa: 'u', \u016f: 'u', \u00fd: 'y', \u017e: 'z' };
	/**
	 * Converts string to web safe characters [a-z0-9-] text.
	 * @param {string} s
	 * @return {string}
	 */
	function webalize(s) {
		s = s.toLowerCase();
		let res = '';
		for (let i = 0; i < s.length; i++) {
			let ch = webalizeTable[s.charAt(i)];
			res += ch ? ch : s.charAt(i);
		}
		return res.replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
	}

	var version = "3.5.3";

	let nette = new FormValidator;
	nette.version = version;
	nette.webalize = webalize;

	return nette;

}));
