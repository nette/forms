import { FormElement, FormElementValue } from './types';
import { FormValidator } from './formValidator';

export class Validators {
	filled(elem: FormElement, arg: undefined, val: FormElementValue): boolean {
		return val !== '' && val !== false && val !== null
			&& (!Array.isArray(val) || val.length > 0)
			&& (!(val instanceof FileList) || val.length > 0);
	}

	blank(elem: FormElement, arg: undefined, val: FormElementValue): boolean {
		return !this.filled(elem, arg, val);
	}

	valid(elem: FormElement, arg: FormValidator) {
		return arg.validateControl(elem, undefined, true);
	}

	equal(elem: FormElement, arg: string | number | boolean | (string | number | boolean)[], val: FormElementValue): boolean | null {
		if (arg === undefined) {
			return null;
		}

		let toString = (val: FormElementValue) => {
			if (typeof val === 'number' || typeof val === 'string') {
				return '' + val;
			} else {
				return val === true ? '1' : '';
			}
		};

		let vals = Array.isArray(val) ? val : [val];
		let args = Array.isArray(arg) ? arg : [arg];
		loop:
		for (let a of vals) {
			for (let b of args) {
				if (toString(a) === toString(b)) {
					continue loop;
				}
			}
			return false;
		}
		return vals.length > 0;
	}

	notEqual(elem: FormElement, arg: string | number | boolean | (string | number | boolean)[], val: FormElementValue): boolean | null {
		return arg === undefined ? null : !this.equal(elem, arg, val);
	}

	minLength(elem: FormElement, arg: number, val: string | number | unknown[]): boolean {
		val = typeof val === 'number' ? val.toString() : val;
		return val.length >= arg;
	}

	maxLength(elem: FormElement, arg: number, val: string | number | unknown[]): boolean {
		val = typeof val === 'number' ? val.toString() : val;
		return val.length <= arg;
	}

	length(elem: FormElement, arg: number | [number | null, number | null], val: string | number | unknown[]): boolean {
		val = typeof val === 'number' ? val.toString() : val;
		arg = Array.isArray(arg) ? arg : [arg, arg];
		return (
			(arg[0] === null || val.length >= arg[0])
			&& (arg[1] === null || val.length <= arg[1])
		);
	}

	email(elem: FormElement, arg: undefined, val: string): boolean {
		return (/^("([ !#-[\]-~]|\\[ -~])+"|[-a-z0-9!#$%&'*+/=?^_`{|}~]+(\.[-a-z0-9!#$%&'*+/=?^_`{|}~]+)*)@([0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)+[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?$/i).test(val);
	}

	url(elem: FormElement, arg: undefined, val: string, newValue: { value: string }): boolean {
		if (!(/^[a-z\d+.-]+:/).test(val)) {
			val = 'https://' + val;
		}
		if ((/^https?:\/\/((([-_0-9a-z\u00C0-\u02FF\u0370-\u1EFF]+\.)*[0-9a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,61}[0-9a-z\u00C0-\u02FF\u0370-\u1EFF])?\.)?[a-z\u00C0-\u02FF\u0370-\u1EFF]([-0-9a-z\u00C0-\u02FF\u0370-\u1EFF]{0,17}[a-z\u00C0-\u02FF\u0370-\u1EFF])?|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|\[[0-9a-f:]{3,39}\])(:\d{1,5})?(\/\S*)?$/i).test(val)) {
			newValue.value = val;
			return true;
		}
		return false;
	}

	regexp(elem: FormElement, arg: string, val: string): boolean | null {
		let parts = typeof arg === 'string' ? arg.match(/^\/(.*)\/([imu]*)$/) : false;
		try {
			return parts && (new RegExp(parts[1]!, parts[2]!.replace('u', ''))).test(val);
		} catch {
			return null;
		}
	}

	pattern(
		elem: FormElement,
		arg: string,
		val: string | FileList,
		newValue: null,
		caseInsensitive: boolean,
	): boolean | null {
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
		} catch {
			return null;
		}
	}

	patternCaseInsensitive(elem: FormElement, arg: string, val: string | FileList): boolean | null {
		return this.pattern(elem, arg, val, null, true);
	}

	numeric(elem: FormElement, arg: undefined, val: string): boolean {
		return (/^[0-9]+$/).test(val);
	}

	integer(elem: FormElement, arg: undefined, val: string, newValue: { value: number }): boolean {
		if ((/^-?[0-9]+$/).test(val)) {
			newValue.value = parseFloat(val);
			return true;
		}
		return false;
	}

	float(elem: FormElement, arg: undefined, val: string, newValue: { value: number }): boolean {
		val = val.replace(/ +/g, '').replace(/,/g, '.');
		if ((/^-?[0-9]*\.?[0-9]+$/).test(val)) {
			newValue.value = parseFloat(val);
			return true;
		}
		return false;
	}

	min(elem: FormElement, arg: string | number, val: string | number): boolean {
		if (Number.isFinite(arg)) {
			val = parseFloat(val as string);
		}
		return val >= arg;
	}

	max(elem: FormElement, arg: string | number, val: string | number): boolean {
		if (Number.isFinite(arg)) {
			val = parseFloat(val as string);
		}
		return val <= arg;
	}

	range(elem: FormElement, arg: [string | number | null, string | number | null], val: string | number): boolean | null {
		if (!Array.isArray(arg)) {
			return null;
		} else if (elem.type === 'time' && arg[0]! > arg[1]!) {
			return val >= arg[0]! || val <= arg[1]!;
		}
		return (arg[0] === null || this.min(elem, arg[0], val))
			&& (arg[1] === null || this.max(elem, arg[1], val));
	}

	submitted(elem: FormElement): boolean {
		return elem.form['nette-submittedBy'] === elem;
	}

	fileSize(elem: FormElement, arg: number, val: FileList): boolean {
		return Array.from(val).every((file) => file.size <= arg);
	}

	mimeType(elem: FormElement, args: string | string[], val: FileList): boolean {
		let parts: string[] = [];
		args = Array.isArray(args) ? args : [args];
		args.forEach((arg) => parts.push('^' + arg.replace(/([^\w])/g, '\\$1').replace('\\*', '.*') + '$'));
		let re = new RegExp(parts.join('|'));
		return Array.from(val).every((file) => !file.type || re.test(file.type));
	}

	image(elem: FormElement, arg: string | string[] | undefined, val: FileList): boolean {
		return this.mimeType(elem, arg ?? ['image/gif', 'image/png', 'image/jpeg', 'image/webp'], val);
	}

	static(elem: FormElement, arg: unknown): unknown {
		return arg;
	}
}
