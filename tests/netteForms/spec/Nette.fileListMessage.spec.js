import { describe, it, expect, beforeEach, afterEach } from 'vitest';

describe('FileList substituted into error message', () => {
	let testContainer;

	beforeEach(() => {
		testContainer = document.createElement('div');
		document.body.appendChild(testContainer);
	});

	afterEach(() => {
		testContainer.remove();
	});

	it('shows file names instead of [object FileList] for %value', () => {
		testContainer.innerHTML = '<form><input type="file" name="avatar"></form>';

		let form = testContainer.querySelector('form'),
			el = form.elements['avatar'];

		el.setAttribute('data-nette-rules', JSON.stringify([
			{ op: ':mimeType', msg: 'Unsupported file %value.', arg: ['image/*'] },
		]));

		let files = [
				new File(['a'], 'photo.txt', { type: 'text/plain' }),
				new File(['b'], 'scan.pdf', { type: 'application/pdf' }),
			],
			fileList = Object.create(FileList.prototype);
		files.forEach((file, i) => Object.defineProperty(fileList, i, { value: file, enumerable: true }));
		Object.defineProperty(fileList, 'length', { value: files.length });
		Object.defineProperty(el, 'files', { value: fileList });

		Nette.formErrors = [];
		expect(Nette.validateControl(el)).toBe(false);
		expect(Nette.formErrors[0].message).toBe('Unsupported file photo.txt, scan.pdf.');
	});
});
