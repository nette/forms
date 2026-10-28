import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';

describe('Bad input in validateForm()', () => {
	let testContainer;

	beforeEach(() => {
		testContainer = document.createElement('div');
		document.body.appendChild(testContainer);
	});

	afterEach(() => {
		testContainer.remove();
	});

	function createForm() {
		testContainer.innerHTML = '<form>'
			+ '<input type="number" name="outside">'
			+ '<input type="text" name="inside">'
			+ '<input type="submit" name="send" formnovalidate data-nette-validation-scope=\'["inside"]\'>'
			+ '</form>';

		let form = testContainer.querySelector('form'),
			el = form.elements['outside'];
		Object.defineProperty(el, 'validity', { value: { badInput: true, valid: false } });
		el.reportValidity = vi.fn();
		return { form, el };
	}

	it('blocks the form and reports the input', () => {
		let { form, el } = createForm();
		expect(Nette.validateForm(form)).toBe(false);
		expect(el.reportValidity).toHaveBeenCalledOnce();
	});

	it('does not report the input when only checking', () => {
		let { form, el } = createForm();
		expect(Nette.validateForm(form, true)).toBe(false);
		expect(el.reportValidity).not.toHaveBeenCalled();
	});

	it('ignores the input outside the validation scope', () => {
		let { form, el } = createForm();
		expect(Nette.validateForm(form.elements['send'])).toBe(true);
		expect(el.reportValidity).not.toHaveBeenCalled();
	});
});
