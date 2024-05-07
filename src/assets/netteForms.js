/**!
 * NetteForms - simple form validation.
 *
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

/**
 * @typedef {HTMLInputElement|HTMLTextAreaElement|HTMLSelectElement|HTMLButtonElement} FormElement
 * @typedef {{op: string, neg: boolean, msg: string, arg: *, rules: ?Array<Rule>, control: string, toggle: ?Array<string>}} Rule
 */

(function (global, factory) {
	if (!global.JSON) {
		return;
	}

	if (typeof define === 'function' && define.amd) {
		define(() => factory(global));
	} else if (typeof module === 'object' && typeof module.exports === 'object') {
		module.exports = factory(global);
	} else {
		let init = !global.Nette?.noInit;
		global.Nette = factory(global);
		if (init) {
			global.Nette.initOnLoad();
		}
	}

}(typeof window !== 'undefined' ? window : this, (window) => {
	'use strict';

	const Nette = {};
	let preventFiltering = {};
	let formToggles = {};
	let toggleListeners = new window.WeakMap();

	Nette.formErrors = [];
	Nette.version = '3.3.0';


	/**
	 * @param {HTMLFormElement} form
	 * @param {string} name
	 * @return {?FormElement}
	 */
	function getFormElement(form, name) {
		let res = form.elements.namedItem(name);
		return res instanceof RadioNodeList ? res[0] : res;
	}


	/**
	 * @param {FormElement} elem
	 * @return {Array<FormElement>}
	 */
	function expandRadioElement(elem) {
		let res = elem.form.elements.namedItem(elem.name);
		return res instanceof RadioNodeList ? Array.from(res) : [res];
	}


	/**
	 * Function to execute when the DOM is fully loaded.
	 * @private
	 */
	Nette.onDocumentReady = function (callback) {
		if (document.readyState !== 'loading') {
			callback.call(this);
		} else {
			document.addEventListener('DOMContentLoaded', callback);
		}
	};


	/**
	 * Returns the value of form element.
	 * @param {FormElement|RadioNodeList} elem
	 * @return {*}
	 */
	Nette.getValue = function (elem) {
		if (elem instanceof HTMLInputElement) {
			if (elem.type === 'radio') {
				return expandRadioElement(elem)
					.find((input) => input.checked)
					?.value ?? null;

			} else if (elem.type === 'file') {
				return elem.files;

			} else if (elem.type === 'checkbox') {
				return elem.name.endsWith('[]') // checkbox list
					? expandRadioElement(elem)
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
			return Nette.getValue(elem[0]);

		} else {
			return null;
		}
	};


	/**
	 * Returns the effective value of form element.
	 * @param {FormElement} elem
	 * @param {boolean} filter
	 * @return {*}
	 */
	Nette.getEffectiveValue = function (elem, filter = false) {
		let val = Nette.getValue(elem);
		if (val === elem.getAttribute('data-nette-empty-value')) {
			val = '';
		}
		if (filter && preventFiltering[elem.name] === undefined) {
			preventFiltering[elem.name] = true;
			let ref = {value: val};
			Nette.validateControl(elem, null, true, ref);
			val = ref.value;
			delete preventFiltering[elem.name];
		}
		return val;
	};


	/**
	 * Validates form element against given rules.
	 * @param {FormElement} elem
	 * @param {?Array<Rule>} rules
	 * @param {boolean} onlyCheck
	 * @param {?{value: *}} value
	 * @param {?boolean} emptyOptional
	 * @return {boolean}
	 */
	Nette.validateControl = function (elem, rules, onlyCheck = false, value = null, emptyOptional = null) {
		rules ??= JSON.parse(elem.getAttribute('data-nette-rules') ?? '[]');
		value ??= {value: Nette.getEffectiveValue(elem)};
		emptyOptional ??= !Nette.validateRule(elem, ':filled', null, value);

		for (let rule of rules) {
			let op = rule.op.match(/(~)?([^?]+)/),
				curElem = rule.control ? getFormElement(elem.form, rule.control) : elem;

			rule.neg = op[1];
			rule.op = op[2];
			rule.condition = !!rule.rules;

			if (!curElem) {
				continue;
			} else if (emptyOptional && !rule.condition && rule.op !== ':filled') {
				continue;
			}

			let success = Nette.validateRule(curElem, rule.op, rule.arg, elem === curElem ? value : undefined);
			if (success === null) {
				continue;
			} else if (rule.neg) {
				success = !success;
			}

			if (rule.condition && success) {
				if (!Nette.validateControl(elem, rule.rules, onlyCheck, value, rule.op === ':blank' ? false : emptyOptional)) {
					return false;
				}
			} else if (!rule.condition && !success) {
				if (Nette.isDisabled(curElem)) {
					continue;
				}
				if (!onlyCheck) {
					let arr = Array.isArray(rule.arg) ? rule.arg : [rule.arg],
						message = rule.msg.replace(
							/%(value|\d+)/g,
							(foo, m) => Nette.getValue(m === 'value' ? curElem : elem.form.elements.namedItem(arr[m].control))
						);
					Nette.addError(curElem, message);
				}
				return false;
			}
		}

		return true;
	};


	/**
	 * Validates whole form.
	 * @param {HTMLFormElement} sender
	 * @param {boolean} onlyCheck
	 * @return {boolean}
	 */
	Nette.validateForm = function (sender, onlyCheck = false) {
		let form = sender.form ?? sender,
			scope;

		Nette.formErrors = [];

		if (form['nette-submittedBy'] && form['nette-submittedBy'].getAttribute('formnovalidate') !== null) {
			let scopeArr = JSON.parse(form['nette-submittedBy'].getAttribute('data-nette-validation-scope') ?? '[]');
			if (scopeArr.length) {
				scope = new RegExp('^(' + scopeArr.join('-|') + '-)');
			} else {
				Nette.showFormErrors(form, []);
				return true;
			}
		}

		for (let elem of form.elements) {
			if (elem.willValidate && elem.validity.badInput) {
				elem.reportValidity();
				return false;
			}
		}

		for (let elem of Array.from(form.elements)) {
			if (elem.getAttribute('data-nette-rules')
				&& (!scope || elem.name.replace(/]\[|\[|]|$/g, '-').match(scope))
				&& !Nette.isDisabled(elem)
				&& !Nette.validateControl(elem, null, onlyCheck)
				&& !Nette.formErrors.length
			) {
				return false;
			}
		}

		let success = !Nette.formErrors.length;
		Nette.showFormErrors(form, Nette.formErrors);
		return success;
	};


	/**
	 * Check if input is disabled.
	 * @param {FormElement} elem
	 * @return {boolean}
	 */
	Nette.isDisabled = function (elem) {
		if (elem.type === 'radio') {
			return expandRadioElement(elem)
				.every((input) => input.disabled);
		}
		return elem.disabled;
	};


	/**
	 * Adds error message to the queue.
	 * @param {FormElement} elem
	 * @param {string} message
	 */
	Nette.addError = function (elem, message) {
		Nette.formErrors.push({
			element: elem,
			message: message
		});
	};


	/**
	 * Display error messages.
	 * @param {HTMLFormElement} form
	 * @param {Array<{element: FormElement, message: string}>} errors
	 */
	Nette.showFormErrors = function (form, errors) {
		let messages = [],
			focusElem;

		for (let error of errors) {
			if (messages.indexOf(error.message) < 0) {
				messages.push(error.message);

				if (!focusElem && error.element.focus) {
					focusElem = error.element;
				}
			}
		}

		if (messages.length) {
			Nette.showModal(messages.join('\n'), () => {
				if (focusElem) {
					focusElem.focus();
				}
			});
		}
	};


	/**
	 * Display modal window.
	 * @param {string} message
	 * @param {function} onclose
	 */
	Nette.showModal = function (message, onclose) {
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
	};


	/**
	 * Validates single rule.
	 * @param {FormElement} elem
	 * @param {string} op
	 * @param {*} arg
	 * @param {?{value: *}} value
	 */
	Nette.validateRule = function (elem, op, arg, value) {
		if (elem.validity.badInput) {
			return op === ':filled';
		}

		value ??= {value: Nette.getEffectiveValue(elem, true)};

		let method = op.charAt(0) === ':' ? op.substring(1) : op;
		method = method.replace('::', '_').replaceAll('\\', '');

		let args = Array.isArray(arg) ? arg : [arg];
		args = args.map((arg) => {
			if (arg?.control) {
				let control = getFormElement(elem.form, arg.control);
				return control === elem ? value.value : Nette.getEffectiveValue(control, true);
			}
			return arg;
		});

		return Nette.validators[method]
			? Nette.validators[method](elem, Array.isArray(arg) ? args : args[0], value.value, value)
			: null;
	};


	Nette.validators = {
		filled: function (elem, arg, val) {
			return val !== '' && val !== false && val !== null
				&& (!Array.isArray(val) || !!val.length)
				&& (!(val instanceof FileList) || val.length);
		},

		blank: function (elem, arg, val) {
			return !Nette.validators.filled(elem, arg, val);
		},

		valid: function (elem) {
			return Nette.validateControl(elem, null, true);
		},

		equal: function (elem, arg, val) {
			if (arg === undefined) {
				return null;
			}

			let toString = (val) => {
				if (typeof val === 'number' || typeof val === 'string') {
					return '' + val;
				} else {
					return val === true ? '1' : '';
				}
			};

			val = Array.isArray(val) ? val : [val];
			arg = Array.isArray(arg) ? arg : [arg];
			loop:
			for (let a of val) {
				for (let b of arg) {
					if (toString(a) === toString(b)) {
						continue loop;
					}
				}
				return false;
			}
			return val.length > 0;
		},

		notEqual: function (elem, arg, val) {
			return arg === undefined ? null : !Nette.validators.equal(elem, arg, val);
		},

		minLength: function (elem, arg, val) {
			val = typeof val === 'number' ? val.toString() : val;
			return val.length >= arg;
		},

		maxLength: function (elem, arg, val) {
			val = typeof val === 'number' ? val.toString() : val;
			return val.length <= arg;
		},

		length: function (elem, arg, val) {
			val = typeof val === 'number' ? val.toString() : val;
			arg = Array.isArray(arg) ? arg : [arg, arg];
			return (arg[0] === null || val.length >= arg[0]) && (arg[1] === null || val.length <= arg[1]);
		},

		email: function (elem, arg, val) {
			return (/^("([ !#-[\]-~]|\\[ -~])+"|[-a-z0-9!#$%&'*+/=?^_`{|}~]+(\.[-a-z0-9!#$%&'*+/=?^_`{|}~]+)*)@([0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)+[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?$/i).test(val);
		},

		url: function (elem, arg, val, newValue) {
			if (!(/^[a-z\d+.-]+:/).test(val)) {
				val = 'https://' + val;
			}
			if ((/^https?:\/\/((([-_0-9a-z\u00C0-\u02FF\u0370-\u1EFF]+\.)*[0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)?[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|\[[0-9a-f:]{3,39}\])(:\d{1,5})?(\/\S*)?$/i).test(val)) {
				newValue.value = val;
				return true;
			}
			return false;
		},

		regexp: function (elem, arg, val) {
			let parts = typeof arg === 'string' ? arg.match(/^\/(.*)\/([imu]*)$/) : false;
			try {
				return parts && (new RegExp(parts[1], parts[2].replace('u', ''))).test(val);
			} catch {} // eslint-disable-line no-empty
		},

		pattern: function (elem, arg, val, newValue, caseInsensitive) {
			if (typeof arg !== 'string') {
				return null;
			}

			try {
				let regExp;
				try {
					regExp = new RegExp('^(?:' + arg + ')$', caseInsensitive ? 'ui' : 'u');
				} catch {
					regExp = new RegExp('^(?:' + arg + ')$', caseInsensitive ? 'i' : '');
				}

				return val instanceof FileList
					? Array.from(val).every((file) => regExp.test(file.name))
					: regExp.test(val);
			} catch {} // eslint-disable-line no-empty
		},

		patternCaseInsensitive: function (elem, arg, val) {
			return Nette.validators.pattern(elem, arg, val, null, true);
		},

		numeric: function (elem, arg, val) {
			return (/^[0-9]+$/).test(val);
		},

		integer: function (elem, arg, val, newValue) {
			if ((/^-?[0-9]+$/).test(val)) {
				newValue.value = parseFloat(val);
				return true;
			}
			return false;
		},

		'float': function (elem, arg, val, newValue) {
			val = val.replace(/ +/g, '').replace(/,/g, '.');
			if ((/^-?[0-9]*\.?[0-9]+$/).test(val)) {
				newValue.value = parseFloat(val);
				return true;
			}
			return false;
		},

		min: function (elem, arg, val) {
			if (Number.isFinite(arg)) {
				val = parseFloat(val);
			}
			return val >= arg;
		},

		max: function (elem, arg, val) {
			if (Number.isFinite(arg)) {
				val = parseFloat(val);
			}
			return val <= arg;
		},

		range: function (elem, arg, val) {
			if (!Array.isArray(arg)) {
				return null;
			} else if (elem.type === 'time' && arg[0] > arg[1]) {
				return val >= arg[0] || val <= arg[1];
			}
			return (arg[0] === null || Nette.validators.min(elem, arg[0], val))
				&& (arg[1] === null || Nette.validators.max(elem, arg[1], val));
		},

		submitted: function (elem) {
			return elem.form['nette-submittedBy'] === elem;
		},

		fileSize: function (elem, arg, val) {
			return Array.from(val).every((file) => file.size <= arg);
		},

		mimeType: function (elem, args, val) {
			let re = [];
			args = Array.isArray(args) ? args : [args];
			args.forEach((arg) => re.push('^' + arg.replace(/([^\w])/g, '\\$1').replace('\\*', '.*') + '$'));
			re = new RegExp(re.join('|'));
			return Array.from(val).every((file) => !file.type || re.test(file.type));
		},

		image: function (elem, arg, val) {
			return Nette.validators.mimeType(elem, arg ?? ['image/gif', 'image/png', 'image/jpeg', 'image/webp'], val);
		},

		'static': function (elem, arg) {
			return arg;
		}
	};


	/**
	 * Process all toggles in form.
	 * @param {HTMLFormElement} form
	 * @param {?Event} event
	 */
	Nette.toggleForm = function (form, event = null) {
		formToggles = {};
		for (let elem of Array.from(form.elements)) {
			if (elem.getAttribute('data-nette-rules')) {
				Nette.toggleControl(elem, null, null, !event);
			}
		}

		for (let i in formToggles) {
			Nette.toggle(i, formToggles[i].state, formToggles[i].elem, event);
		}
	};


	/**
	 * Process toggles on form element.
	 * @param {FormElement} elem
	 * @param {?Array<Rule>} rules
	 * @param {?boolean} success
	 * @param {boolean} firsttime
	 * @param {?{value: *}} value
	 * @param {?boolean} emptyOptional
	 * @return {boolean}
	 */
	Nette.toggleControl = function (elem, rules, success, firsttime, value = null, emptyOptional = null) {
		rules ??= JSON.parse(elem.getAttribute('data-nette-rules') ?? '[]');
		value ??= {value: Nette.getEffectiveValue(elem)};
		emptyOptional ??= !Nette.validateRule(elem, ':filled', null, value);

		let has = false,
			curSuccess;

		for (let rule of rules) {
			let op = rule.op.match(/(~)?([^?]+)/),
				curElem = rule.control ? getFormElement(elem.form, rule.control) : elem;

			rule.neg = op[1];
			rule.op = op[2];
			rule.condition = !!rule.rules;

			if (!curElem) {
				continue;
			} else if (emptyOptional && !rule.condition && rule.op !== ':filled') {
				continue;
			}

			curSuccess = success;
			if (success !== false) {
				curSuccess = Nette.validateRule(curElem, rule.op, rule.arg, elem === curElem ? value : undefined);
				if (curSuccess === null) {
					continue;

				} else if (rule.neg) {
					curSuccess = !curSuccess;
				}
				if (!rule.condition) {
					success = curSuccess;
				}
			}

			if ((rule.condition && Nette.toggleControl(elem, rule.rules, curSuccess, firsttime, value, rule.op === ':blank' ? false : emptyOptional)) || rule.toggle) {
				has = true;
				if (firsttime) {
					expandRadioElement(curElem)
						.filter((el) => !toggleListeners.has(el))
						.forEach((el) => {
							el.addEventListener('change', (e) => Nette.toggleForm(elem.form, e));
							toggleListeners.set(el, null);
						});
				}
				for (let id in rule.toggle ?? []) {
					formToggles[id] ??= {elem: elem};
					formToggles[id].state ||= rule.toggle[id] ? curSuccess : !curSuccess;
				}
			}
		}
		return has;
	};


	/**
	 * Displays or hides HTML element.
	 * @param {string} selector
	 * @param {boolean} visible
	 * @param {FormElement} srcElement
	 * @param {Event} event
	 */
	Nette.toggle = function (selector, visible, srcElement, event) { // eslint-disable-line no-unused-vars
		if (/^\w[\w.:-]*$/.test(selector)) { // id
			selector = '#' + selector;
		}
		Array.from(document.querySelectorAll(selector))
			.forEach((elem) => elem.hidden = !visible);
	};


	/**
	 * Compact checkboxes
	 * @param {HTMLFormElement} form
	 * @param {FormData} formData
	 */
	Nette.compactCheckboxes = function (form, formData) {
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
	};


	/**
	 * Setup handlers.
	 * @param {HTMLFormElement} form
	 */
	Nette.initForm = function (form) {
		if (form.method === 'get' && form.hasAttribute('data-nette-compact')) {
			form.addEventListener('formdata', (e) => Nette.compactCheckboxes(form, e.formData));
		}

		if (!Array.from(form.elements).some((elem) => elem.getAttribute('data-nette-rules'))) {
			return;
		}

		Nette.toggleForm(form);

		if (form.noValidate) {
			return;
		}
		form.noValidate = true;

		form.addEventListener('submit', (e) => {
			if (!Nette.validateForm(form)) {
				e.stopPropagation();
				e.preventDefault();
			}
		});

		form.addEventListener('reset', () => {
			setTimeout(() => Nette.toggleForm(form));
		});
	};


	/**
	 * @private
	 */
	Nette.initOnLoad = function () {
		Nette.onDocumentReady(() => {
			Array.from(document.forms)
				.forEach((form) => Nette.initForm(form));

			document.body.addEventListener('click', (e) => {
				let target = e.target;
				while (target) {
					if (target.form && target.type in {submit: 1, image: 1}) {
						target.form['nette-submittedBy'] = target;
						break;
					}
					target = target.parentNode;
				}
			});
		});
	};


	/**
	 * Converts string to web safe characters [a-z0-9-] text.
	 * @param {string} s
	 * @return {string}
	 */
	Nette.webalize = function (s) {
		s = s.toLowerCase();
		let res = '', ch;
		for (let i = 0; i < s.length; i++) {
			ch = Nette.webalizeTable[s.charAt(i)];
			res += ch ? ch : s.charAt(i);
		}
		return res.replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
	};

	Nette.webalizeTable = {\u00e1: 'a', \u00e4: 'a', \u010d: 'c', \u010f: 'd', \u00e9: 'e', \u011b: 'e', \u00ed: 'i', \u013e: 'l', \u0148: 'n', \u00f3: 'o', \u00f4: 'o', \u0159: 'r', \u0161: 's', \u0165: 't', \u00fa: 'u', \u016f: 'u', \u00fd: 'y', \u017e: 'z'};

	return Nette;
}));
