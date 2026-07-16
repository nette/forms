import { describe, it, expect, beforeEach, vi } from 'vitest';

// Nette.repeater is initialized by the UMD bundle loaded in setup.js;
// a delegated click listener on document handles all repeaters

function createMarkup() {
	document.body.innerHTML = `
		<form>
			<div data-nette-repeater="persons" data-nette-repeater-min="1" data-nette-repeater-max="5">
				<div data-repeater-index="0">
					<input name="persons[0][firstname]" id="frm-persons-0-firstname" value="John">
					<input name="persons[0][surname]" id="frm-persons-0-surname" value="Doe">
					<button type="button" data-nette-repeater-remove>Remove</button>
				</div>
				<div data-repeater-index="1">
					<input name="persons[1][firstname]" id="frm-persons-1-firstname" value="Jane">
					<input name="persons[1][surname]" id="frm-persons-1-surname" value="Smith">
					<button type="button" data-nette-repeater-remove>Remove</button>
				</div>
				<template>
					<div data-repeater-index="" class="row">
						<input name="[firstname]" id="frm--firstname">
						<input name="[surname]" id="frm--surname" data-nette-rules='[{"op":":filled","msg":"Fill it","control":"[firstname]"}]'>
						<input name="external_helper" value="widget">
						<button type="button" data-nette-repeater-remove>Remove</button>
					</div>
				</template>
			</div>
			<button type="button" data-nette-repeater-add="persons">Add person</button>
		</form>
	`;
	return document.querySelector('[data-nette-repeater="persons"]');
}


describe('Nette.repeater', () => {
	let container;

	beforeEach(() => {
		container = createMarkup();
	});


	describe('adding items', () => {
		it('adds new item to container', () => {
			document.querySelector('[data-nette-repeater-add]').click();

			expect(container.querySelectorAll('[data-repeater-index]').length).toBe(3);
		});

		it('assigns the next free index and rewrites names and ids', () => {
			document.querySelector('[data-nette-repeater-add]').click();

			const newItem = container.querySelector('[data-repeater-index="2"]');
			expect(newItem).not.toBeNull();
			expect(newItem.querySelector('[name="persons[2][firstname]"]')).not.toBeNull();
			expect(newItem.querySelector('#frm-persons-2-firstname')).not.toBeNull();
		});

		it('rewrites relative control references in validation rules', () => {
			document.querySelector('[data-nette-repeater-add]').click();

			const surname = container.querySelector('[name="persons[2][surname]"]');
			const rules = JSON.parse(surname.getAttribute('data-nette-rules'));
			expect(rules[0].control).toBe('persons[2][firstname]');
		});

		it('clones the wrapper element from the template including its attributes', () => {
			document.querySelector('[data-nette-repeater-add]').click();

			const newItem = container.querySelector('[data-repeater-index="2"]');
			expect(newItem.className).toBe('row');
		});

		it('leaves absolute names untouched', () => {
			document.querySelector('[data-nette-repeater-add]').click();

			const newItem = container.querySelector('[data-repeater-index="2"]');
			expect(newItem.querySelector('[name="external_helper"]')).not.toBeNull();
		});

		it('creates a div wrapper for hand-written templates without one', () => {
			container.querySelector('template').content.querySelector('[data-repeater-index]').replaceWith(
				...container.querySelector('template').content.querySelector('[data-repeater-index]').childNodes,
			);
			document.querySelector('[data-nette-repeater-add]').click();

			const newItem = container.querySelector('[data-repeater-index="2"]');
			expect(newItem.tagName).toBe('DIV');
			expect(newItem.querySelector('[name="persons[2][firstname]"]')).not.toBeNull();
		});

		it('dispatches repeater:added event after insertion', () => {
			const listener = vi.fn((e) => {
				expect(e.detail.item.isConnected).toBe(true);
			});
			container.addEventListener('repeater:added', listener);

			document.querySelector('[data-nette-repeater-add]').click();

			expect(listener).toHaveBeenCalled();
			expect(listener.mock.calls[0][0].detail.index).toBe(2);
			expect(listener.mock.calls[0][0].cancelable).toBe(false);
		});

		it('does not collide with existing indexes after removal', () => {
			const addButton = document.querySelector('[data-nette-repeater-add]');
			container.querySelector('[data-repeater-index="0"]').remove();

			addButton.click();

			// item with index 1 exists, the new one must get index 2
			expect(container.querySelectorAll('[data-repeater-index="2"]').length).toBe(1);
		});

		it('respects maximum limit and disables the add button', () => {
			const addButton = document.querySelector('[data-nette-repeater-add]');

			addButton.click(); // 3
			addButton.click(); // 4
			addButton.click(); // 5
			addButton.click(); // beyond max, ignored

			expect(container.querySelectorAll('[data-repeater-index]').length).toBe(5);
			expect(addButton.disabled).toBe(true);
		});

		it('dispatches cancelable repeater:add event', () => {
			const listener = vi.fn((e) => e.preventDefault());
			container.addEventListener('repeater:add', listener);

			document.querySelector('[data-nette-repeater-add]').click();

			expect(listener).toHaveBeenCalled();
			expect(listener.mock.calls[0][0].detail.index).toBe(2);
			expect(container.querySelectorAll('[data-repeater-index]').length).toBe(2); // not added
		});
	});


	describe('removing items', () => {
		it('removes item from container', () => {
			container.querySelector('[data-repeater-index="1"] [data-nette-repeater-remove]').click();

			expect(container.querySelectorAll('[data-repeater-index]').length).toBe(1);
			expect(container.querySelector('[data-repeater-index="0"]')).not.toBeNull();
		});

		it('respects minimum limit', () => {
			container.querySelector('[data-repeater-index="1"] [data-nette-repeater-remove]').click();
			container.querySelector('[data-repeater-index="0"] [data-nette-repeater-remove]').click(); // below min, ignored

			expect(container.querySelectorAll('[data-repeater-index]').length).toBe(1);
		});

		it('dispatches cancelable repeater:remove event with modified flag', () => {
			const item = container.querySelector('[data-repeater-index="0"]');
			item.querySelector('[name="persons[0][firstname]"]').value = 'Modified';

			const listener = vi.fn((e) => e.preventDefault());
			container.addEventListener('repeater:remove', listener);

			item.querySelector('[data-nette-repeater-remove]').click();

			expect(listener).toHaveBeenCalled();
			expect(listener.mock.calls[0][0].detail.modified).toBe(true);
			expect(container.querySelectorAll('[data-repeater-index]').length).toBe(2); // not removed
		});
	});


	describe('hasModifiedFields', () => {
		it('detects modified text input', () => {
			const item = container.querySelector('[data-repeater-index="0"]');

			expect(Nette.repeater.hasModifiedFields(item)).toBe(false);

			item.querySelector('[name="persons[0][firstname]"]').value = 'Modified';
			expect(Nette.repeater.hasModifiedFields(item)).toBe(true);
		});

		it('detects modified checkbox', () => {
			const item = container.querySelector('[data-repeater-index="0"]');
			item.innerHTML += '<input type="checkbox" name="test" checked>';

			const checkbox = item.querySelector('[type="checkbox"]');
			expect(Nette.repeater.hasModifiedFields(item)).toBe(false);

			checkbox.checked = false;
			expect(Nette.repeater.hasModifiedFields(item)).toBe(true);
		});

		it('detects modified select', () => {
			const item = container.querySelector('[data-repeater-index="0"]');
			item.innerHTML += '<select name="test"><option value="a">A</option><option value="b" selected>B</option></select>';

			const select = item.querySelector('select');
			select.options[1].defaultSelected = true;

			expect(Nette.repeater.hasModifiedFields(item)).toBe(false);

			select.options[0].selected = true;
			expect(Nette.repeater.hasModifiedFields(item)).toBe(true);
		});
	});


	describe('nested repeaters', () => {
		beforeEach(() => {
			document.body.innerHTML = `
				<form>
					<div data-nette-repeater="persons" data-nette-repeater-min="0" data-nette-repeater-max="3">
						<div data-repeater-index="0">
							<input name="persons[0][firstname]" id="frm-persons-0-firstname">

							<div data-nette-repeater="persons-0-emails" data-nette-repeater-min="0" data-nette-repeater-max="2">
								<div data-repeater-index="0">
									<input name="persons[0][emails][0][email]" id="frm-persons-0-emails-0-email">
								</div>
								<template>
									<div data-repeater-index="">
										<input name="[email]" id="frm--email">
									</div>
								</template>
							</div>
							<button type="button" data-nette-repeater-add="persons-0-emails">Add email</button>
						</div>
						<template>
							<div data-repeater-index="">
								<input name="[firstname]" id="frm--firstname">

								<div data-nette-repeater="emails" data-nette-repeater-min="0" data-nette-repeater-max="2">
									<template>
										<div data-repeater-index="">
											<input name="[email]" id="frm--email">
										</div>
									</template>
								</div>
								<button type="button" data-nette-repeater-add="emails">Add email</button>
							</div>
						</template>
					</div>
					<button type="button" data-nette-repeater-add="persons">Add person</button>
				</form>
			`;

			container = document.querySelector('[data-nette-repeater="persons"]');
		});

		it('rewrites nested repeater paths when cloning parent', () => {
			document.querySelector('[data-nette-repeater-add="persons"]').click();

			const newItem = container.querySelector('[data-repeater-index="1"]');
			const nested = newItem.querySelector('[data-nette-repeater]');
			expect(nested.getAttribute('data-nette-repeater')).toBe('persons-1-emails');
			expect(newItem.querySelector('[data-nette-repeater-add]').getAttribute('data-nette-repeater-add')).toBe('persons-1-emails');
		});

		it('can add items to existing nested repeater', () => {
			document.querySelector('[data-nette-repeater-add="persons-0-emails"]').click();

			const nested = document.querySelector('[data-nette-repeater="persons-0-emails"]');
			expect(nested.querySelectorAll(':scope > [data-repeater-index]').length).toBe(2);
			expect(nested.querySelector('[name="persons[0][emails][1][email]"]')).not.toBeNull();
		});

		it('can add items to a freshly cloned nested repeater', () => {
			document.querySelector('[data-nette-repeater-add="persons"]').click();
			container.querySelector('[data-repeater-index="1"] [data-nette-repeater-add]').click();

			const nested = document.querySelector('[data-nette-repeater="persons-1-emails"]');
			expect(nested.querySelector('[name="persons[1][emails][0][email]"]')).not.toBeNull();
			expect(nested.querySelector('#frm-persons-1-emails-0-email')).not.toBeNull();
		});
	});


	describe('table layout', () => {
		beforeEach(() => {
			document.body.innerHTML = `
				<form>
					<table>
						<tbody data-nette-repeater="items" data-nette-repeater-min="0" data-nette-repeater-max="5">
							<tr data-repeater-index="0">
								<td><input name="items[0][description]" id="frm-items-0-description"></td>
							</tr>
							<template>
								<tr data-repeater-index="" class="invoice-row">
									<td><input name="[description]" id="frm--description"></td>
								</tr>
							</template>
						</tbody>
					</table>
					<button type="button" data-nette-repeater-add="items">Add row</button>
				</form>
			`;
			container = document.querySelector('[data-nette-repeater="items"]');
		});

		it('clones table rows as items', () => {
			document.querySelector('[data-nette-repeater-add="items"]').click();

			const newItem = container.querySelector('[data-repeater-index="1"]');
			expect(newItem.tagName).toBe('TR');
			expect(newItem.className).toBe('invoice-row');
			expect(newItem.parentElement.tagName).toBe('TBODY');
			expect(newItem.querySelector('td [name="items[1][description]"]')).not.toBeNull();
		});
	});
});
