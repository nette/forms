// @ts-nocheck
export class Validators {
	filled(elem, arg, val) {
		return val !== '' && val !== false && val !== null
			&& (!Array.isArray(val) || !!val.length)
			&& (!(val instanceof FileList) || val.length);
	}

	blank(elem, arg, val) {
		return !this.filled(elem, arg, val);
	}

	valid(elem, arg) {
		return arg.validateControl(elem, null, true);
	}

	equal(elem, arg, val) {
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
		return (arg[0] === null || val.length >= arg[0]) && (arg[1] === null || val.length <= arg[1]);
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
		} catch {} // eslint-disable-line no-empty
	}

	pattern(elem, arg, val, newValue, caseInsensitive) {
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
		} else if (elem.type === 'time' && arg[0] > arg[1]) {
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
		let re = [];
		args = Array.isArray(args) ? args : [args];
		args.forEach((arg) => re.push('^' + arg.replace(/([^\w])/g, '\\$1').replace('\\*', '.*') + '$'));
		re = new RegExp(re.join('|'));
		return Array.from(val).every((file) => !file.type || re.test(file.type));
	}

	image(elem, arg, val) {
		return this.mimeType(elem, arg ?? ['image/gif', 'image/png', 'image/jpeg', 'image/webp'], val);
	}

	static(elem, arg) {
		return arg;
	}
}
