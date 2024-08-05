describe('ES module build', () => {
	let esm;

	beforeAll(async () => {
		document.body.innerHTML = '<form id="f" action=""><input type="text" name="a" data-nette-rules=\'[{"op":":filled","msg":"Required"}]\'></form>';
		esm = await import('../../../src/assets/dist/nette-forms.mjs');
	});

	it('default export is a ready instance', () => {
		expect(esm.default.validators).toBeDefined();
		expect(typeof esm.default.version).toBe('string');
		expect(typeof esm.default.initOnLoad).toBe('function');
	});

	it('is not auto-initialized on import', () => {
		const form = document.getElementById('f');
		expect(form.noValidate).toBe(false);
		expect(form.hasAttribute('novalidate')).toBe(false);
	});

	it('initOnLoad() initializes forms', () => {
		esm.default.initOnLoad();
		expect(document.getElementById('f').noValidate).toBe(true);
	});

	it('exports named classes', () => {
		expect(typeof esm.FormValidator).toBe('function');
		expect(esm.default).toBeInstanceOf(esm.FormValidator);
	});
});
