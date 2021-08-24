/**
 * NetteForms - simple form validation.
 *
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

(function(global, factory) {
	if (!global.JSON) {
		return;
	}

	if (typeof define === 'function' && define.amd) {
		define(function() {
			return factory(global);
		});
	} else if (typeof module === 'object' && typeof module.exports === 'object') {
		module.exports = factory(global);
	} else {
		var init = !global.Nette || !global.Nette.noInit;
		global.Nette = factory(global);
		if (init) {
			global.Nette.initOnLoad();
		}
	}

}(typeof window !== 'undefined' ? window : this, function(window) {

	'use strict';

	var Nette = {};
	var preventFiltering = {};
	var formToggles = {};
	var toggleListeners = new window.WeakMap();

	Nette.formErrors = [];
	Nette.version = '3.0';
	Nette.invalidNumberMessage = 'Please enter a valid value.';


	/**
	 * Function to execute when the DOM is fully loaded.
	 * @private
	 */
	Nette.onDocumentReady = function(callback) {
		if (document.readyState !== 'loading') {
			callback.call(this);
		} else {
			document.addEventListener('DOMContentLoaded', callback);
		}
	};


	/**
	 * Returns the value of form element.
	 */
	Nette.getValue = function(elem) {
		var i;
		if (!elem) {
			return null;

		} else if (!elem.tagName) { // RadioNodeList, HTMLCollection, array
			return elem[0] ? Nette.getValue(elem[0]) : null;

		} else if (elem.type === 'radio') {
			var elements = elem.form.elements; // prevents problem with name 'item' or 'namedItem'
			for (i = 0; i < elements.length; i++) {
				if (elements[i].name === elem.name && elements[i].checked) {
					return elements[i].value;
				}
			}
			return null;

		} else if (elem.type === 'file') {
			return elem.files || elem.value;

		} else if (elem.tagName.toLowerCase() === 'select') {
			var index = elem.selectedIndex,
				options = elem.options,
				values = [];

			if (elem.type === 'select-one') {
				return index < 0 ? null : options[index].value;
			}

			for (i = 0; i < options.length; i++) {
				if (options[i].selected) {
					values.push(options[i].value);
				}
			}
			return values;

		} else if (elem.name && elem.name.substr(-2) === '[]') { // multiple elements []
			elements = elem.form.elements[elem.name].tagName ? [elem] : elem.form.elements[elem.name];
			values = [];

			for (i = 0; i < elements.length; i++) {
				if (elements[i].type !== 'checkbox' || elements[i].checked) {
					values.push(elements[i].value);
				}
			}
			return values;

		} else if (elem.type === 'checkbox') {
			return elem.checked;

		} else if (elem.tagName.toLowerCase() === 'textarea') {
			return elem.value.replace('\r', '');

		} else {
			return elem.value.replace('\r', '').replace(/^\s+|\s+$/g, '');
		}
	};


	/**
	 * Returns the effective value of form element.
	 */
	Nette.getEffectiveValue = function(elem, filter) {
		var val = Nette.getValue(elem);
		if (elem.getAttribute) {
			if (val === elem.getAttribute('data-nette-empty-value')) {
				val = '';
			}
		}
		if (filter && preventFiltering[elem.name] === undefined) {
			preventFiltering[elem.name] = true;
			var ref = {value: val};
			Nette.validateControl(elem, null, true, ref);
			val = ref.value;
			delete preventFiltering[elem.name];
		}
		return val;
	};


	/**
	 * Validates form element against given rules.
	 */
	Nette.validateControl = function(elem, rules, onlyCheck, value, emptyOptional) {
		var top = !rules;
		elem = elem.tagName ? elem : elem[0]; // RadioNodeList
		rules = rules || JSON.parse(elem.getAttribute('data-nette-rules') || '[]');
		value = value === undefined ? {value: Nette.getEffectiveValue(elem)} : value;
		emptyOptional = emptyOptional === undefined ? !Nette.validateRule(elem, ':filled', null, value) : emptyOptional;

		for (var id = 0, len = rules.length; id < len; id++) {
			var rule = rules[id],
				op = rule.op.match(/(~)?([^?]+)/),
				curElem = rule.control ? elem.form.elements.namedItem(rule.control) : elem;

			rule.neg = op[1];
			rule.op = op[2];
			rule.condition = !!rule.rules;

			if (!curElem) {
				continue;
			} else if (emptyOptional && !rule.condition && rule.op !== ':filled') {
				continue;
			}

			curElem = curElem.tagName ? curElem : curElem[0]; // RadioNodeList
			var success = Nette.validateRule(curElem, rule.op, rule.arg, elem === curElem ? value : undefined);

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
					var arr = Array.isArray(rule.arg) ? rule.arg : [rule.arg],
						message = rule.msg.replace(/%(value|\d+)/g, function(foo, m) {
							return Nette.getValue(m === 'value' ? curElem : elem.form.elements.namedItem(arr[m].control));
						});
					Nette.addError(curElem, message);
				}
				return false;
			}
		}

		if (elem.type === 'number' && !elem.validity.valid) {
			if (top && !onlyCheck) {
				Nette.addError(elem, Nette.invalidNumberMessage);
			}
			return false;
		}

		return true;
	};


	/**
	 * Validates whole form.
	 */
	Nette.validateForm = function(sender, onlyCheck) {
		var form = sender.form || sender,
			scope = false;

		Nette.formErrors = [];

		if (form['nette-submittedBy'] && form['nette-submittedBy'].getAttribute('formnovalidate') !== null) {
			var scopeArr = JSON.parse(form['nette-submittedBy'].getAttribute('data-nette-validation-scope') || '[]');
			if (scopeArr.length) {
				scope = new RegExp('^(' + scopeArr.join('-|') + '-)');
			} else {
				Nette.showFormErrors(form, []);
				return true;
			}
		}

		var radios = {}, i, elem;

		for (i = 0; i < form.elements.length; i++) {
			elem = form.elements[i];

			if (elem.tagName && !(elem.tagName.toLowerCase() in {input: 1, select: 1, textarea: 1, button: 1})) {
				continue;

			} else if (elem.type === 'radio') {
				if (radios[elem.name]) {
					continue;
				}
				radios[elem.name] = true;
			}

			if ((scope && !elem.name.replace(/]\[|\[|]|$/g, '-').match(scope)) || Nette.isDisabled(elem)) {
				continue;
			}

			if (!Nette.validateControl(elem, null, onlyCheck) && !Nette.formErrors.length) {
				return false;
			}
		}
		var success = !Nette.formErrors.length;
		Nette.showFormErrors(form, Nette.formErrors);
		return success;
	};


	/**
	 * Check if input is disabled.
	 */
	Nette.isDisabled = function(elem) {
		if (elem.type === 'radio') {
			for (var i = 0, elements = elem.form.elements; i < elements.length; i++) {
				if (elements[i].name === elem.name && !elements[i].disabled) {
					return false;
				}
			}
			return true;
		}
		return elem.disabled;
	};


	/**
	 * Adds error message to the queue.
	 */
	Nette.addError = function(elem, message) {
		Nette.formErrors.push({
			element: elem,
			message: message
		});
	};


	/**
	 * Display error messages.
	 */
	Nette.showFormErrors = function(form, errors) {
		var messages = [],
			focusElem;

		for (var i = 0; i < errors.length; i++) {
			var elem = errors[i].element,
				message = errors[i].message;

			if (messages.indexOf(message) < 0) {
				messages.push(message);

				if (!focusElem && elem.focus) {
					focusElem = elem;
				}
			}
		}

		if (messages.length) {
			alert(messages.join('\n'));

			if (focusElem) {
				focusElem.focus();
			}
		}
	};


	/**
	 * Validates single rule.
	 */
	Nette.validateRule = function(elem, op, arg, value) {
		value = value === undefined ? {value: Nette.getEffectiveValue(elem, true)} : value;

		if (op.charAt(0) === ':') {
			op = op.substr(1);
		}
		op = op.replace('::', '_');
		op = op.replace(/\\/g, '');

		var arr = Array.isArray(arg) ? arg.slice(0) : [arg];
		for (var i = 0, len = arr.length; i < len; i++) {
			if (arr[i] && arr[i].control) {
				var control = elem.form.elements.namedItem(arr[i].control);
				arr[i] = control === elem ? value.value : Nette.getEffectiveValue(control, true);
			}
		}

		return Nette.validators[op]
			? Nette.validators[op](elem, Array.isArray(arg) ? arr : arr[0], value.value, value)
			: null;
	};


	Nette.validators = {
		filled: function(elem, arg, val) {
			if (elem.type === 'number' && elem.validity.badInput) {
				return true;
			}
			return val !== '' && val !== false && val !== null
				&& (!Array.isArray(val) || !!val.length)
				&& (!window.FileList || !(val instanceof window.FileList) || val.length);
		},

		blank: function(elem, arg, val) {
			return !Nette.validators.filled(elem, arg, val);
		},

		valid: function(elem) {
			return Nette.validateControl(elem, null, true);
		},

		equal: function(elem, arg, val) {
			if (arg === undefined) {
				return null;
			}

			var toString = function(val) {
				if (typeof val === 'number' || typeof val === 'string') {
					return '' + val;
				} else {
					return val === true ? '1' : '';
				}
			};

			val = Array.isArray(val) ? val : [val];
			arg = Array.isArray(arg) ? arg : [arg];
			loop:
			for (var i1 = 0, len1 = val.length; i1 < len1; i1++) {
				for (var i2 = 0, len2 = arg.length; i2 < len2; i2++) {
					if (toString(val[i1]) === toString(arg[i2])) {
						continue loop;
					}
				}
				return false;
			}
			return val.length > 0;
		},

		notEqual: function(elem, arg, val) {
			return arg === undefined ? null : !Nette.validators.equal(elem, arg, val);
		},

		minLength: function(elem, arg, val) {
			if (elem.type === 'number') {
				if (elem.validity.tooShort) {
					return false;
				} else if (elem.validity.badInput) {
					return null;
				}
			}
			return val.length >= arg;
		},

		maxLength: function(elem, arg, val) {
			if (elem.type === 'number') {
				if (elem.validity.tooLong) {
					return false;
				} else if (elem.validity.badInput) {
					return null;
				}
			}
			return val.length <= arg;
		},

		length: function(elem, arg, val) {
			if (elem.type === 'number') {
				if (elem.validity.tooShort || elem.validity.tooLong) {
					return false;
				} else if (elem.validity.badInput) {
					return null;
				}
			}
			arg = Array.isArray(arg) ? arg : [arg, arg];
			return (arg[0] === null || val.length >= arg[0]) && (arg[1] === null || val.length <= arg[1]);
		},

		email: function(elem, arg, val) {
			return (/^("([ !#-[\]-~]|\\[ -~])+"|[-a-z0-9!#$%&'*+/=?^_`{|}~]+(\.[-a-z0-9!#$%&'*+/=?^_`{|}~]+)*)@([0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)+[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?$/i).test(val);
		},

		url: function(elem, arg, val, value) {
			if (!(/^[a-z\d+.-]+:/).test(val)) {
				val = 'https://' + val;
			}
			if ((/^https?:\/\/((([-_0-9a-z\u00C0-\u02FF\u0370-\u1EFF]+\.)*[0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)?[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|\[[0-9a-f:]{3,39}\])(:\d{1,5})?(\/\S*)?$/i).test(val)) {
				value.value = val;
				return true;
			}
			return false;
		},

		regexp: function(elem, arg, val) {
			var parts = typeof arg === 'string' ? arg.match(/^\/(.*)\/([imu]*)$/) : false;
			try {
				return parts && (new RegExp(parts[1], parts[2].replace('u', ''))).test(val);
			} catch (e) {} // eslint-disable-line no-empty
		},

		pattern: function(elem, arg, val, value, caseInsensitive) {
			if (typeof arg !== 'string') {
				return null;
			}

			try {
				try {
					var regExp = new RegExp('^(?:' + arg + ')$', caseInsensitive ? 'ui' : 'u');
				} catch (e) {
					regExp = new RegExp('^(?:' + arg + ')$', caseInsensitive ? 'i' : '');
				}

				if (window.FileList && val instanceof FileList) {
					for (var i = 0; i < val.length; i++) {
						if (!regExp.test(val[i].name)) {
							return false;
						}
					}

					return true;
				}

				return regExp.test(val);
			} catch (e) {} // eslint-disable-line no-empty
		},

		patternCaseInsensitive: function(elem, arg, val) {
			return Nette.validators.pattern(elem, arg, val, null, true);
		},

		numeric: function(elem, arg, val) {
			if (elem.type === 'number' && elem.validity.badInput) {
				return false;
			}
			return (/^[0-9]+$/).test(val);
		},

		integer: function(elem, arg, val) {
			if (elem.type === 'number' && elem.validity.badInput) {
				return false;
			}
			return (/^-?[0-9]+$/).test(val);
		},

		'float': function(elem, arg, val, value) {
			if (elem.type === 'number' && elem.validity.badInput) {
				return false;
			}
			val = val.replace(/ +/g, '').replace(/,/g, '.');
			if ((/^-?[0-9]*\.?[0-9]+$/).test(val)) {
				value.value = val;
				return true;
			}
			return false;
		},

		min: function(elem, arg, val) {
			if (elem.type === 'number') {
				if (elem.validity.rangeUnderflow) {
					return false;
				} else if (elem.validity.badInput) {
					return null;
				}
			}
			return arg === null || parseFloat(val) >= arg;
		},

		max: function(elem, arg, val) {
			if (elem.type === 'number') {
				if (elem.validity.rangeOverflow) {
					return false;
				} else if (elem.validity.badInput) {
					return null;
				}
			}
			return arg === null || parseFloat(val) <= arg;
		},

		range: function(elem, arg, val) {
			if (elem.type === 'number') {
				if (elem.validity.rangeUnderflow || elem.validity.rangeOverflow) {
					return false;
				} else if (elem.validity.badInput) {
					return null;
				}
			}
			return Array.isArray(arg) ?
				((arg[0] === null || parseFloat(val) >= arg[0]) && (arg[1] === null || parseFloat(val) <= arg[1])) : null;
		},

		submitted: function(elem) {
			return elem.form['nette-submittedBy'] === elem;
		},

		fileSize: function(elem, arg, val) {
			if (window.FileList) {
				for (var i = 0; i < val.length; i++) {
					if (val[i].size > arg) {
						return false;
					}
				}
			}
			return true;
		},

		mimeType: function (elem, arg, val) {
			arg = Array.isArray(arg) ? arg : [arg];
			for (var i = 0, len = arg.length, re = []; i < len; i++) {
				re.push('^' + arg[i].replace(/([^\w])/g, '\\$1').replace('\\*', '.*') + '$');
			}
			re = new RegExp(re.join('|'));

			if (window.FileList && val instanceof FileList) {
				for (i = 0; i < val.length; i++) {
					if (val[i].type && !re.test(val[i].type)) {
						return false;
					} else if (elem.validity.badInput) {
						return null;
					}
				}
			}
			return true;
		},

		image: function (elem, arg, val) {
			return Nette.validators.mimeType(elem, ['image/gif', 'image/png', 'image/jpeg', 'image/webp'], val);
		},

		'static': function (elem, arg) {
			return arg;
		}
	};


	/**
	 * Process all toggles in form.
	 */
	Nette.toggleForm = function(form, event) {
		var i;
		formToggles = {};
		for (i = 0; i < form.elements.length; i++) {
			if (form.elements[i].tagName.toLowerCase() in {input: 1, select: 1, textarea: 1, button: 1}) {
				Nette.toggleControl(form.elements[i], null, null, !event);
			}
		}

		for (i in formToggles) {
			Nette.toggle(i, formToggles[i].state, formToggles[i].elem, event);
		}
	};


	/**
	 * Process toggles on form element.
	 */
	Nette.toggleControl = function(elem, rules, success, firsttime, value, emptyOptional) {
		rules = rules || JSON.parse(elem.getAttribute('data-nette-rules') || '[]');
		value = value === undefined ? {value: Nette.getEffectiveValue(elem)} : value;
		emptyOptional = emptyOptional === undefined ? !Nette.validateRule(elem, ':filled', null, value) : emptyOptional;

		var has = false,
			handler = function (e) {
				Nette.toggleForm(elem.form, e);
			},
			curSuccess;

		for (var id = 0, len = rules.length; id < len; id++) {
			var rule = rules[id],
				op = rule.op.match(/(~)?([^?]+)/),
				curElem = rule.control ? elem.form.elements.namedItem(rule.control) : elem;

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
					var name = curElem.tagName ? curElem.name : curElem[0].name,
						els = curElem.tagName ? curElem.form.elements : curElem;

					for (var i = 0; i < els.length; i++) {
						if (els[i].name === name && !toggleListeners.has(els[i])) {
							els[i].addEventListener('change', handler);
							toggleListeners.set(els[i], null);
						}
					}
				}
				for (var toggleId in rule.toggle || []) {
					formToggles[toggleId] = formToggles[toggleId] || {elem: elem};
					formToggles[toggleId].state = formToggles[toggleId].state || (rule.toggle[toggleId] ? curSuccess : !curSuccess);
				}
			}
		}
		return has;
	};


	/**
	 * Displays or hides HTML element.
	 */
	Nette.toggle = function(selector, visible, srcElement, event) { // eslint-disable-line no-unused-vars
		if (/^\w[\w.:-]*$/.test(selector)) { // id
			selector = '#' + selector;
		}
		var elems = document.querySelectorAll(selector);
		for (var i = 0; i < elems.length; i++) {
			elems[i].hidden = !visible;
		}
	};


	/**
	 * Compact checkboxes
	 */
	Nette.compactCheckboxes = function(form) {
		var name, i, elem, values = {};

		for (i = 0; i < form.elements.length; i++) {
			elem = form.elements[i];
			if (elem.tagName
				&& elem.tagName.toLowerCase() === 'input'
				&& elem.type === 'checkbox'
			) {
				if (elem.name
					&& elem.name.substr(-2) === '[]'
				) {
					name = elem.name.substr(0, elem.name.length - 2);
					elem.removeAttribute('name');
					elem.setAttribute('data-nette-name', name);
				}

				if (name = elem.getAttribute('data-nette-name')) { // eslint-disable-line no-cond-assign
					values[name] = values[name] || [];
					if (elem.checked && !elem.disabled) {
						values[name].push(elem.value);
					}
				}
			}
		}

		for (name in values) {
			if (form.elements[name] === undefined) {
				elem = document.createElement('input');
				elem.setAttribute('name', name);
				elem.setAttribute('type', 'hidden');
				form.appendChild(elem);
			}
			form.elements[name].value = values[name].join(',');
			form.elements[name].disabled = values[name].length === 0;
		}
	};


	/**
	 * Setup handlers.
	 */
	Nette.initForm = function(form) {
		if (form.method === 'get' && form.hasAttribute('data-nette-compact')) {
			form.addEventListener('submit', function() {
				Nette.compactCheckboxes(form);
			});
		}

		check: {
			for (var i = 0; i < form.elements.length; i++) {
				if (form.elements[i].getAttribute('data-nette-rules')) {
					break check;
				}
			}
			return;
		}

		Nette.toggleForm(form);

		if (form.noValidate) {
			return;
		}
		form.noValidate = true;

		form.addEventListener('submit', function(e) {
			if (!Nette.validateForm(form)) {
				e.stopPropagation();
				e.preventDefault();
			}
		});
	};


	/**
	 * @private
	 */
	Nette.initOnLoad = function() {
		Nette.onDocumentReady(function() {
			for (var i = 0; i < document.forms.length; i++) {
				Nette.initForm(document.forms[i]);
			}

			document.body.addEventListener('click', function(e) {
				var target = e.target;
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
	 */
	Nette.webalize = function(s) {
		s = s.toLowerCase();
		var res = '', i, ch;
		for (i = 0; i < s.length; i++) {
			ch = Nette.webalizeTable[s.charAt(i)];
			res += ch ? ch : s.charAt(i);
		}
		return res.replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
	};

	Nette.webalizeTable = {\u00e1: 'a', \u00e4: 'a', \u010d: 'c', \u010f: 'd', \u00e9: 'e', \u011b: 'e', \u00ed: 'i', \u013e: 'l', \u0148: 'n', \u00f3: 'o', \u00f4: 'o', \u0159: 'r', \u0161: 's', \u0165: 't', \u00fa: 'u', \u016f: 'u', \u00fd: 'y', \u017e: 'z'};

	return Nette;
}));
