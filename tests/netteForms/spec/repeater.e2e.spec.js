import { describe, it, expect } from 'vitest';
import fs from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

// The fixtures are HTML rendered by PHP, see tests/netteForms/fixtures/generate.php

const fixtures = join(dirname(fileURLToPath(import.meta.url)), '../fixtures');

function load(file) {
	document.body.innerHTML = fs.readFileSync(join(fixtures, file), 'utf-8');
	return document.querySelector('form');
}


describe('repeater with HTML rendered by PHP', () => {
	describe('unnamed form rendered by Latte', () => {
		it('adds a row with rewritten names, ids, labels, datalist and rules', () => {
			const form = load('repeater-latte.html');
			form.querySelector('[data-nette-repeater-add="persons"]').click();

			const row = form.querySelector('[data-nette-repeater="persons"] > [data-repeater-index="1"]');
			expect(row).not.toBeNull();
			expect(row.innerHTML).not.toContain('__index0__');

			const name = row.querySelector('[name="persons[1][name]"]');
			expect(name.id).toBe('frm-persons-1-name');
			expect(row.querySelector('label[for="frm-persons-1-name"]')).not.toBeNull();
			expect(name.getAttribute('list')).toBe('frm-persons-1-name-list');
			expect(row.querySelector('datalist#frm-persons-1-name-list option[value="Joe"]')).not.toBeNull();
			expect(row.querySelector('input[type=hidden][name="persons[1][id]"]')).not.toBeNull();

			// the nested repeater with its default item and its own template
			const email = row.querySelector('[name="persons[1][emails][0][email]"]');
			expect(email.id).toBe('frm-persons-1-emails-0-email');
			const rules = JSON.parse(email.getAttribute('data-nette-rules'));
			expect(rules[1].control).toBe('persons[1][name]');
			expect(row.querySelector('[data-nette-repeater="persons-1-emails"]')).not.toBeNull();
			expect(row.querySelector('[data-nette-repeater-add="persons-1-emails"]')).not.toBeNull();
		});

		it('adds a row to a nested repeater of an added row', () => {
			const form = load('repeater-latte.html');
			form.querySelector('[data-nette-repeater-add="persons"]').click();
			form.querySelector('[data-nette-repeater-add="persons-1-emails"]').click();

			const nested = form.querySelector('[data-nette-repeater="persons-1-emails"]');
			const email = nested.querySelector(':scope > [data-repeater-index="1"] input');
			expect(email.name).toBe('persons[1][emails][1][email]');
			expect(email.id).toBe('frm-persons-1-emails-1-email');
			expect(nested.querySelector('label[for="frm-persons-1-emails-1-email"]')).not.toBeNull();
			expect(JSON.parse(email.getAttribute('data-nette-rules'))[1].control).toBe('persons[1][name]');
		});

		it('adds a row to a nested repeater of a rendered row', () => {
			const form = load('repeater-latte.html');
			form.querySelector('[data-nette-repeater-add="persons-0-emails"]').click();

			const email = form.querySelector('[name="persons[0][emails][1][email]"]');
			expect(email.id).toBe('frm-persons-0-emails-1-email');
			expect(JSON.parse(email.getAttribute('data-nette-rules'))[1].control).toBe('persons[0][name]');
		});

		it('removes a row together with its hidden field', () => {
			const form = load('repeater-latte.html');
			form.querySelector('[data-nette-repeater-add="persons"]').click();
			form.querySelector('[data-nette-repeater="persons"] > [data-repeater-index="0"] > [data-nette-repeater-remove]').click();

			expect(form.querySelector('[name="persons[0][name]"]')).toBeNull();
			expect(form.querySelector('[name="persons[0][id]"]')).toBeNull();
			expect(form.querySelector('[name="persons[1][name]"]')).not.toBeNull();
			expect(new FormData(form).has('persons[0][id]')).toBe(false);
		});

		it('submits only the rows in the document, not the template', () => {
			const form = load('repeater-latte.html');
			form.querySelector('[data-nette-repeater-add="persons"]').click();

			const names = [...new FormData(form).keys()];
			expect(names).toEqual([
				'persons[0][name]',
				'persons[0][emails][0][email]',
				'persons[0][id]',
				'persons[1][name]',
				'persons[1][emails][0][email]',
				'persons[1][id]',
			]);
		});
	});


	describe('table rows rendered by n:repeater-item', () => {
		it('adds a row whose hidden field and datalist stay with it', () => {
			const form = load('repeater-table.html');
			form.querySelector('[data-nette-repeater-add="rows"]').click();

			const row = form.querySelector('tbody > tr[data-repeater-index="1"]');
			const input = row.querySelector('input[name="rows[1][product]"]');
			expect(input.id).toBe('frm-invoice-rows-1-product');
			expect(input.getAttribute('list')).toBe('frm-invoice-rows-1-product-list');
			expect(row.querySelector('datalist#frm-invoice-rows-1-product-list option[value="Pear"]')).not.toBeNull();
			expect(row.querySelector('input[type=hidden][name="rows[1][id]"]')).not.toBeNull();

			row.querySelector('[data-nette-repeater-remove]').click();
			expect(form.querySelector('#frm-invoice-rows-1-product-list')).toBeNull();
			expect(new FormData(form).has('rows[1][id]')).toBe(false);
		});
	});


	describe('named form rendered by DefaultFormRenderer', () => {
		it('adds and removes rows', () => {
			const form = load('repeater-renderer.html');
			const add = form.querySelector('[data-nette-repeater-add="items"]');
			add.click();
			add.click();

			const container = form.querySelector('[data-nette-repeater="items"]');
			expect(container.querySelectorAll(':scope > [data-repeater-index]').length).toBe(3);
			const input = container.querySelector('[data-repeater-index="2"] input');
			expect(input.name).toBe('items[2][description]');
			expect(input.id).toBe('frm-order-items-2-description');
			expect(container.querySelector('[data-repeater-index="2"] label').htmlFor).toBe('frm-order-items-2-description');

			container.querySelector('[data-repeater-index="1"] [data-nette-repeater-remove]').click();
			expect([...container.querySelectorAll(':scope > [data-repeater-index]')].map((el) => el.dataset.repeaterIndex)).toEqual(['0', '2']);
		});
	});
});
