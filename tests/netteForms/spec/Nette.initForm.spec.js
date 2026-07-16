import { describe, it, expect } from 'vitest';

describe('Nette.initForm', () => {

	it('wires validation when fields are injected after the first init', () => {
		document.body.innerHTML = '<form method="post"><input type="text" name="plain"></form>';
		let form = document.querySelector('form');

		Nette.initForm(form);
		expect(form.noValidate).toBe(false); // nothing to validate yet

		form.insertAdjacentHTML('beforeend', '<input type="text" name="req" data-nette-rules=\'[{"op":":filled","msg":"req"}]\'>');
		Nette.initForm(form);
		expect(form.noValidate).toBe(true); // submit validation installed on re-init

		Nette.initForm(form); // idempotent
		expect(form.noValidate).toBe(true);
	});


	it('initializes a form whose fields start only in a repeater template', () => {
		document.body.innerHTML = `
			<form method="post">
				<div data-nette-repeater="items" data-nette-repeater-min="0">
					<template><input type="text" name="[name]" data-nette-rules='[{"op":":filled","msg":"req"}]'></template>
				</div>
			</form>`;
		let form = document.querySelector('form');

		Nette.initForm(form);
		expect(form.noValidate).toBe(true);
	});

});
