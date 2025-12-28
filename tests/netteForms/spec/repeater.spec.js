import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';

describe('Nette.Repeater', () => {
	let manager;
	let container;

	beforeEach(() => {
		// Create a DOM structure for testing
		document.body.innerHTML = `
			<div data-nette-repeater="persons" data-nette-repeater-min="1" data-nette-repeater-max="5">
				<div data-repeater-index="0">
					<input name="persons[0][firstname]" id="frm-persons-0-firstname" value="John">
					<input name="persons[0][surname]" id="frm-persons-0-surname" value="Doe">
					<button data-nette-repeater-remove>Remove</button>
				</div>
				<div data-repeater-index="1">
					<input name="persons[1][firstname]" id="frm-persons-1-firstname" value="Jane">
					<input name="persons[1][surname]" id="frm-persons-1-surname" value="Smith">
					<button data-nette-repeater-remove>Remove</button>
				</div>
				<template>
					<input name="[firstname]" id="frm--firstname">
					<input name="[surname]" id="frm--surname">
					<button data-nette-repeater-remove>Remove</button>
				</template>
			</div>
			<button data-nette-repeater-add="persons">Add person</button>
		`;

		container = document.querySelector('[data-nette-repeater="persons"]');
		manager = new RepeaterManager();
	});

	afterEach(() => {
		vi.restoreAllMocks();
	});


	describe('cloneTemplate', () => {
		it('creates new item with correct index', () => {
			const template = container.querySelector('template');
			const clone = manager.cloneTemplate(template, 'persons', 2);

			expect(clone.dataset.repeaterIndex).toBe('2');
		});

		it('converts relative paths to absolute paths', () => {
			const template = container.querySelector('template');
			const clone = manager.cloneTemplate(template, 'persons', 2);

			const firstnameInput = clone.querySelector('[name*="firstname"]');
			expect(firstnameInput.getAttribute('name')).toBe('persons[2][firstname]');

			const surnameInput = clone.querySelector('[name*="surname"]');
			expect(surnameInput.getAttribute('name')).toBe('persons[2][surname]');
		});

		it('updates id attributes', () => {
			const template = container.querySelector('template');
			const clone = manager.cloneTemplate(template, 'persons', 2);

			const firstnameInput = clone.querySelector('[id*="firstname"]');
			expect(firstnameInput.getAttribute('id')).toBe('frm-persons-2-firstname');
		});
	});


	describe('handleAdd', () => {
		it('adds new item to container', () => {
			const addButton = document.querySelector('[data-nette-repeater-add]');
			const initialCount = container.querySelectorAll('[data-repeater-index]').length;

			addButton.click();

			const newCount = container.querySelectorAll('[data-repeater-index]').length;
			expect(newCount).toBe(initialCount + 1);
		});

		it('assigns correct index to new item', () => {
			const addButton = document.querySelector('[data-nette-repeater-add]');

			addButton.click();

			const newItem = container.querySelector('[data-repeater-index="2"]');
			expect(newItem).not.toBeNull();
		});

		it('respects maximum limit', () => {
			const addButton = document.querySelector('[data-nette-repeater-add]');

			// Add items up to max (5)
			addButton.click(); // index 2
			addButton.click(); // index 3
			addButton.click(); // index 4

			vi.spyOn(window, 'alert').mockImplementation(() => {});

			// Try to add beyond max
			addButton.click();

			expect(window.alert).toHaveBeenCalled();
			expect(container.querySelectorAll('[data-repeater-index]').length).toBe(5);
		});

		it('initializes event listeners on new item', () => {
			const addButton = document.querySelector('[data-nette-repeater-add]');

			addButton.click();

			const newItem = container.querySelector('[data-repeater-index="2"]');
			const removeButton = newItem.querySelector('[data-nette-repeater-remove]');

			expect(removeButton).not.toBeNull();
		});
	});


	describe('handleRemove', () => {
		it('removes item from container', () => {
			const removeButton = container.querySelectorAll('[data-nette-repeater-remove]')[1];
			const initialCount = container.querySelectorAll('[data-repeater-index]').length;

			removeButton.click();

			const newCount = container.querySelectorAll('[data-repeater-index]').length;
			expect(newCount).toBe(initialCount - 1);
		});

		it('respects minimum limit', () => {
			const removeButtons = container.querySelectorAll('[data-nette-repeater-remove]');

			// Remove first item (will have 1 left)
			removeButtons[1].click();

			vi.spyOn(window, 'alert').mockImplementation(() => {});

			// Try to remove last item (below min=1)
			const remainingButton = container.querySelector('[data-nette-repeater-remove]');
			remainingButton.click();

			expect(window.alert).toHaveBeenCalled();
			expect(container.querySelectorAll('[data-repeater-index]').length).toBe(1);
		});

		it('renumbers remaining items after removal', () => {
			const removeButton = container.querySelector('[data-repeater-index="0"] [data-nette-repeater-remove]');

			removeButton.click();

			// Item that was at index 1 should now be at index 0
			const item = container.querySelector('[data-repeater-index="0"]');
			expect(item).not.toBeNull();

			const firstnameInput = item.querySelector('[name*="firstname"]');
			expect(firstnameInput.getAttribute('name')).toBe('persons[0][firstname]');
			expect(firstnameInput.getAttribute('id')).toBe('frm-persons-0-firstname');
			expect(firstnameInput.value).toBe('Jane'); // Original value from index 1
		});

		it('asks for confirmation when removing modified item', () => {
			const item = container.querySelector('[data-repeater-index="0"]');
			const input = item.querySelector('[name*="firstname"]');

			// Modify the input
			input.value = 'Modified';

			vi.spyOn(window, 'confirm').mockReturnValue(false);

			const removeButton = item.querySelector('[data-nette-repeater-remove]');
			removeButton.click();

			expect(window.confirm).toHaveBeenCalledWith('This item has been modified. Do you really want to remove it?');
			expect(container.querySelectorAll('[data-repeater-index]').length).toBe(2); // Not removed
		});

		it('removes modified item when confirmed', () => {
			const item = container.querySelector('[data-repeater-index="0"]');
			const input = item.querySelector('[name*="firstname"]');

			// Modify the input
			input.value = 'Modified';

			vi.spyOn(window, 'confirm').mockReturnValue(true);

			const removeButton = item.querySelector('[data-nette-repeater-remove]');
			removeButton.click();

			expect(window.confirm).toHaveBeenCalled();
			expect(container.querySelectorAll('[data-repeater-index]').length).toBe(1); // Removed
		});

		it('removes unmodified item without confirmation', () => {
			vi.spyOn(window, 'confirm');

			const removeButton = container.querySelector('[data-repeater-index="0"] [data-nette-repeater-remove]');
			removeButton.click();

			expect(window.confirm).not.toHaveBeenCalled();
			expect(container.querySelectorAll('[data-repeater-index]').length).toBe(1); // Removed
		});
	});


	describe('hasModifiedFields', () => {
		it('detects modified text input', () => {
			const item = container.querySelector('[data-repeater-index="0"]');
			const input = item.querySelector('[name*="firstname"]');

			expect(manager.hasModifiedFields(item)).toBe(false);

			input.value = 'Modified';
			expect(manager.hasModifiedFields(item)).toBe(true);
		});

		it('detects modified checkbox', () => {
			const item = container.querySelector('[data-repeater-index="0"]');
			item.innerHTML += '<input type="checkbox" name="test" checked>';

			const checkbox = item.querySelector('[type="checkbox"]');
			checkbox.defaultChecked = true;

			expect(manager.hasModifiedFields(item)).toBe(false);

			checkbox.checked = false;
			expect(manager.hasModifiedFields(item)).toBe(true);
		});

		it('detects modified select', () => {
			const item = container.querySelector('[data-repeater-index="0"]');
			item.innerHTML += '<select name="test"><option value="a">A</option><option value="b" selected>B</option></select>';

			const select = item.querySelector('select');
			select.options[1].defaultSelected = true;

			expect(manager.hasModifiedFields(item)).toBe(false);

			select.options[0].selected = true;
			expect(manager.hasModifiedFields(item)).toBe(true);
		});

		it('returns false when all fields are unchanged', () => {
			const item = container.querySelector('[data-repeater-index="0"]');

			expect(manager.hasModifiedFields(item)).toBe(false);
		});
	});


	describe('renumberItems', () => {
		it('updates data-repeater-index attributes', () => {
			// Remove first item
			container.querySelector('[data-repeater-index="0"]').remove();

			manager.renumberItems(container);

			const items = container.querySelectorAll('[data-repeater-index]');
			expect(items[0].dataset.repeaterIndex).toBe('0');
		});

		it('updates name attributes', () => {
			// Remove first item
			container.querySelector('[data-repeater-index="0"]').remove();

			manager.renumberItems(container);

			const firstInput = container.querySelector('[data-repeater-index="0"] [name*="firstname"]');
			expect(firstInput.getAttribute('name')).toBe('persons[0][firstname]');
		});

		it('updates id attributes', () => {
			// Remove first item
			container.querySelector('[data-repeater-index="0"]').remove();

			manager.renumberItems(container);

			const firstInput = container.querySelector('[data-repeater-index="0"] [id*="firstname"]');
			expect(firstInput.getAttribute('id')).toBe('frm-persons-0-firstname');
		});
	});


	describe('nested repeaters', () => {
		beforeEach(() => {
			document.body.innerHTML = `
				<div data-nette-repeater="persons" data-nette-repeater-min="0" data-nette-repeater-max="3">
					<div data-repeater-index="0">
						<input name="persons[0][firstname]" id="frm-persons-0-firstname">

						<div data-nette-repeater="emails" data-nette-repeater-min="0" data-nette-repeater-max="2">
							<div data-repeater-index="0">
								<input name="persons[0][emails][0][email]" id="frm-persons-0-emails-0-email">
							</div>
							<template>
								<input name="[email]" id="frm--email">
							</template>
						</div>
						<button data-nette-repeater-add="emails">Add email</button>
					</div>
					<template>
						<input name="[firstname]" id="frm-0-firstname">

						<div data-nette-repeater="emails" data-nette-repeater-min="0" data-nette-repeater-max="2">
							<template>
								<input name="[email]" id="frm--email">
							</template>
						</div>
						<button data-nette-repeater-add="emails">Add email</button>
					</template>
				</div>
				<button data-nette-repeater-add="persons">Add person</button>
			`;

			container = document.querySelector('[data-nette-repeater="persons"]');
			manager = new RepeaterManager();
		});

		it('updates nested template paths when cloning parent', () => {
			const addButton = document.querySelector('[data-nette-repeater-add="persons"]');

			addButton.click();

			const newItem = container.querySelector('[data-repeater-index="1"]');
			const nestedContainer = newItem.querySelector('[data-nette-repeater="emails"]');

			expect(nestedContainer.dataset.netteRepeaterPath).toBe('persons-1-emails');
		});

		it('can add items to nested repeater', () => {
			const emailsContainer = document.querySelector('[data-nette-repeater="emails"]');
			const addEmailButton = document.querySelector('[data-nette-repeater-add="emails"]');

			addEmailButton.click();

			const items = emailsContainer.querySelectorAll('[data-repeater-index]');
			expect(items.length).toBe(2);
		});
	});
});
