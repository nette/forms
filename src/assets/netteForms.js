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

	Nette.formErrors = [];
	Nette.version = '2.4';


	/**
	 * Attaches a handler to an event for the element.
	 */
	Nette.addEvent = function(element, on, callback) {
		if (on === 'DOMContentLoaded' && element.readyState !== 'loading') {
			callback.call(this);
		} else if (element.addEventListener) {
			element.addEventListener(on, callback);
		} else if (on === 'DOMContentLoaded') {
			element.attachEvent('onreadystatechange', function() {
				if (element.readyState === 'complete') {
					callback.call(this);
				}
			});
		} else {
			element.attachEvent('on' + on, getHandler(callback));
		}
	};


	function getHandler(callback) {
		return function(e) {
			return callback.call(this, e);
		};
	}


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

		} else if (elem.name && elem.name.match(/\[\]$/)) { // multiple elements []
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
	Nette.getEffectiveValue = function(elem) {
		var val = Nette.getValue(elem);
		if (elem.getAttribute) {
			if (val === elem.getAttribute('data-nette-empty-value')) {
				val = '';
			}
		}
		return val;
	};


	/**
	 * Validates form element against given rules.
	 */
	Nette.validateControl = function(elem, rules, onlyCheck, value, emptyOptional) {
		elem = elem.tagName ? elem : elem[0]; // RadioNodeList
		rules = rules || Nette.parseJSON(elem.getAttribute('data-nette-rules'));
		value = value === undefined ? {value: Nette.getEffectiveValue(elem)} : value;

		for (var id = 0, len = rules.length; id < len; id++) {
			var rule = rules[id],
				op = rule.op.match(/(~)?([^?]+)/),
				curElem = rule.control ? elem.form.elements.namedItem(rule.control) : elem;

			rule.neg = op[1];
			rule.op = op[2];
			rule.condition = !!rule.rules;

			if (!curElem) {
				continue;
			} else if (rule.op === 'optional') {
				emptyOptional = !Nette.validateRule(elem, ':filled', null, value);
				continue;
			} else if (emptyOptional && !rule.condition && rule.op !== ':filled') {
				continue;
			}

			curElem = curElem.tagName ? curElem : curElem[0]; // RadioNodeList
			var curValue = elem === curElem ? value : {value: Nette.getEffectiveValue(curElem)},
				success = Nette.validateRule(curElem, rule.op, rule.arg, curValue);

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
					var arr = Nette.isArray(rule.arg) ? rule.arg : [rule.arg],
						message = rule.msg.replace(/%(value|\d+)/g, function(foo, m) {
							return Nette.getValue(m === 'value' ? curElem : elem.form.elements.namedItem(arr[m].control));
						});
					Nette.addError(curElem, message);
				}
				return false;
			}
		}

		if (elem.type === 'number' && !elem.validity.valid) {
			if (!onlyCheck) {
				Nette.addError(elem, 'Please enter a valid value.');
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
			var scopeArr = Nette.parseJSON(form['nette-submittedBy'].getAttribute('data-nette-validation-scope'));
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

			if (!Nette.inArray(messages, message)) {
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
	 * Expand rule argument.
	 */
	Nette.expandRuleArgument = function(form, arg) {
		if (arg && arg.control) {
			var control = form.elements.namedItem(arg.control),
				value = {value: Nette.getEffectiveValue(control)};
			Nette.validateControl(control, null, true, value);
			arg = value.value;
		}
		return arg;
	};


	var preventFiltering = false;

	/**
	 * Validates single rule.
	 */
	Nette.validateRule = function(elem, op, arg, value) {
		value = value === undefined ? {value: Nette.getEffectiveValue(elem)} : value;

		if (op.charAt(0) === ':') {
			op = op.substr(1);
		}
		op = op.replace('::', '_');
		op = op.replace(/\\/g, '');

		var arr = Nette.isArray(arg) ? arg.slice(0) : [arg];
		if (!preventFiltering) {
			preventFiltering = true;
			for (var i = 0, len = arr.length; i < len; i++) {
				arr[i] = Nette.expandRuleArgument(elem.form, arr[i]);
			}
			preventFiltering = false;
		}
		return Nette.validators[op]
			? Nette.validators[op](elem, Nette.isArray(arg) ? arr : arr[0], value.value, value)
			: null;
	};


	Nette.validators = {
		filled: function(elem, arg, val) {
			if (elem.type === 'number' && elem.validity.badInput) {
				return true;
			}
			return val !== '' && val !== false && val !== null
				&& (!Nette.isArray(val) || !!val.length)
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

			function toString(val) {
				if (typeof val === 'number' || typeof val === 'string') {
					return '' + val;
				} else {
					return val === true ? '1' : '';
				}
			}

			val = Nette.isArray(val) ? val : [val];
			arg = Nette.isArray(arg) ? arg : [arg];
			loop:
			for (var i1 = 0, len1 = val.length; i1 < len1; i1++) {
				for (var i2 = 0, len2 = arg.length; i2 < len2; i2++) {
					if (toString(val[i1]) === toString(arg[i2])) {
						continue loop;
					}
				}
				return false;
			}
			return true;
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
			arg = Nette.isArray(arg) ? arg : [arg, arg];
			return (arg[0] === null || val.length >= arg[0]) && (arg[1] === null || val.length <= arg[1]);
		},

		email: function(elem, arg, val) {
			return (/^("([ !#-[\]-~]|\\[ -~])+"|[-a-z0-9!#$%&'*+/=?^_`{|}~]+(\.[-a-z0-9!#$%&'*+/=?^_`{|}~]+)*)@([0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)+[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?$/i).test(val);
		},

		url: function(elem, arg, val, value) {
			if (!(/^[a-z\d+.-]+:/).test(val)) {
				val = 'http://' + val;
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

		pattern: function(elem, arg, val) {
			try {
				return typeof arg === 'string' ? (new RegExp('^(?:' + arg + ')$')).test(val) : null;
			} catch (e) {} // eslint-disable-line no-empty
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
			return Nette.isArray(arg) ?
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

		image: function (elem, arg, val) {
			if (window.FileList && val instanceof window.FileList) {
				for (var i = 0; i < val.length; i++) {
					var type = val[i].type;
					if (type && type !== 'image/gif' && type !== 'image/png' && type !== 'image/jpeg') {
						return false;
					}
				}
			}
			return true;
		},

		'static': function (elem, arg) {
			return arg;
		}
	};


	/**
	 * Process all toggles in form.
	 */
	Nette.toggleForm = function(form, elem) {
		var i;
		Nette.toggles = {};
		for (i = 0; i < form.elements.length; i++) {
			if (form.elements[i].tagName.toLowerCase() in {input: 1, select: 1, textarea: 1, button: 1}) {
				Nette.toggleControl(form.elements[i], null, null, !elem);
			}
		}

		for (i in Nette.toggles) {
			Nette.toggle(i, Nette.toggles[i], elem);
		}
	};


	/**
	 * Process toggles on form element.
	 */
	Nette.toggleControl = function(elem, rules, success, firsttime, value) {
		rules = rules || Nette.parseJSON(elem.getAttribute('data-nette-rules'));
		value = value === undefined ? {value: Nette.getEffectiveValue(elem)} : value;

		var has = false,
			handled = [],
			handler = function () {
				Nette.toggleForm(elem.form, elem);
			},
			curSuccess;

		for (var id = 0, len = rules.length; id < len; id++) {
			var rule = rules[id],
				op = rule.op.match(/(~)?([^?]+)/),
				curElem = rule.control ? elem.form.elements.namedItem(rule.control) : elem;

			if (!curElem) {
				continue;
			}

			curSuccess = success;
			if (success !== false) {
				rule.neg = op[1];
				rule.op = op[2];
				var curValue = elem === curElem ? value : {value: Nette.getEffectiveValue(curElem)};
				curSuccess = Nette.validateRule(curElem, rule.op, rule.arg, curValue);
				if (curSuccess === null) {
					continue;

				} else if (rule.neg) {
					curSuccess = !curSuccess;
				}
				if (!rule.rules) {
					success = curSuccess;
				}
			}

			if ((rule.rules && Nette.toggleControl(elem, rule.rules, curSuccess, firsttime, value)) || rule.toggle) {
				has = true;
				if (firsttime) {
					var oldIE = !document.addEventListener, // IE < 9
						name = curElem.tagName ? curElem.name : curElem[0].name,
						els = curElem.tagName ? curElem.form.elements : curElem;

					for (var i = 0; i < els.length; i++) {
						if (els[i].name === name && !Nette.inArray(handled, els[i])) {
							Nette.addEvent(els[i], oldIE && els[i].type in {checkbox: 1, radio: 1} ? 'click' : 'change', handler);
							handled.push(els[i]);
						}
					}
				}
				for (var id2 in rule.toggle || []) {
					if (Object.prototype.hasOwnProperty.call(rule.toggle, id2)) {
						Nette.toggles[id2] = Nette.toggles[id2] || (rule.toggle[id2] ? curSuccess : !curSuccess);
					}
				}
			}
		}
		return has;
	};


	Nette.parseJSON = function(s) {
		return (s || '').substr(0, 3) === '{op'
			? eval('[' + s + ']') // backward compatibility with Nette 2.0.x
			: JSON.parse(s || '[]');
	};


	/**
	 * Displays or hides HTML element.
	 */
	Nette.toggle = function(id, visible, srcElement) { // eslint-disable-line no-unused-vars
		var elem = document.getElementById(id);
		if (elem) {
			elem.style.display = visible ? '' : 'none';
		}
	};


	/**
	 * Setup handlers.
	 */
	Nette.initForm = function(form) {
		Nette.toggleForm(form);

		if (form.noValidate) {
			return;
		}

		form.noValidate = true;

		Nette.addEvent(form, 'submit', function(e) {
			if (!Nette.validateForm(form)) {
				if (e && e.stopPropagation) {
					e.stopPropagation();
					e.preventDefault();
				} else if (window.event) {
					event.cancelBubble = true;
					event.returnValue = false;
				}
			}
		});
	};


	/**
	 * @private
	 */
	Nette.initOnLoad = function() {
		Nette.addEvent(document, 'DOMContentLoaded', function() {
			for (var i = 0; i < document.forms.length; i++) {
				var form = document.forms[i];
				for (var j = 0; j < form.elements.length; j++) {
					if (form.elements[j].getAttribute('data-nette-rules')) {
						Nette.initForm(form);
						break;
					}
				}
			}

			Nette.addEvent(document.body, 'click', function(e) {
				var target = e.target || e.srcElement;
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
	 * Determines whether the argument is an array.
	 */
	Nette.isArray = function(arg) {
		return Object.prototype.toString.call(arg) === '[object Array]';
	};


	/**
	 * Search for a specified value within an array.
	 */
	Nette.inArray = function(arr, val) {
		if ([].indexOf) {
			return arr.indexOf(val) > -1;
		} else {
			for (var i = 0; i < arr.length; i++) {
				if (arr[i] === val) {
					return true;
				}
			}
			return false;
		}
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
