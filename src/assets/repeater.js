/**
 * Nette Forms - Repeater Manager
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

const IdPrefix = 'frm-';
const NameSeparator = '-';

class RepeaterManager {
	constructor() {
		this.init(document);
	}


	init(context) {
		// Add event listeners for add buttons
		context.querySelectorAll('[data-nette-repeater-add]').forEach(btn => {
			btn.addEventListener('click', (e) => this.handleAdd(e));
		});

		// Add event listeners for remove buttons
		context.querySelectorAll('[data-nette-repeater-remove]').forEach(btn => {
			btn.addEventListener('click', (e) => this.handleRemove(e));
		});
	}


	handleAdd(e) {
		const button = e.target;
		const repeaterName = button.dataset.netteRepeaterAdd;

		// Find repeater container (previous sibling or closest)
		const container = button.previousElementSibling; // TODO
		if (!container || !container.dataset.netteRepeater) {
			console.error('Repeater container not found');
			return;
		}

		const max = container.dataset.netteRepeaterMax;
		const name = container.dataset.netteRepeater;

		// Find template (direct child only, not nested)
		const template = container.querySelector(':scope > template');
		if (!template) {
			console.error('Template not found');
			return;
		}
		if (!name) {
			console.error('Repeater name not found');
			return;
		}

		// Get new index
		const existingItems = container.querySelectorAll(':scope > [data-repeater-index]');
		const newIndex = existingItems.length;

		// Check max
		if (max && newIndex >= parseInt(max)) {
			alert('Maximum number of items reached: ' + max);
			return;
		}

		// Clone template
		const clone = this.cloneTemplate(template, name, newIndex);

		// Insert before template
		container.insertBefore(clone, template);

		// Reinit event listeners on new elements
		this.init(clone);
	}


	handleRemove(e) {
		const button = e.target;
		const item = button.closest('[data-repeater-index]');

		if (!item) {
			console.error('Item not found');
			return;
		}

		const container = item.parentElement;
		const min = container.dataset.netteRepeaterMin;

		// Count existing items
		const existingItems = container.querySelectorAll(':scope > [data-repeater-index]');

		if (min && existingItems.length <= parseInt(min)) {
			alert('Minimum number of items: ' + min);
			return;
		}

		// Check if any field has been modified
		if (this.hasModifiedFields(item)) {
			if (!confirm('This item has been modified. Do you really want to remove it?')) {
				return;
			}
		}

		// Remove item
		item.remove();
	}


	hasModifiedFields(element) {
		// Check inputs and textareas
		const inputs = element.querySelectorAll('input, textarea');
		for (const input of inputs) {
			if (input.type === 'checkbox' || input.type === 'radio') {
				if (input.checked !== input.defaultChecked) {
					return true;
				}
			} else {
				if (input.value !== input.defaultValue) {
					return true;
				}
			}
		}

		// Check selects
		const selects = element.querySelectorAll('select');
		for (const select of selects) {
			const options = select.options;
			for (let i = 0; i < options.length; i++) {
				if (options[i].selected !== options[i].defaultSelected) {
					return true;
				}
			}
		}

		return false;
	}


	cloneTemplate(templateEl, repeaterName, newIndex) {
		const clone = templateEl.content.cloneNode(true);
		repeaterName += NameSeparator + newIndex;

		// Convert repeaterName from "foo-1-test" to "foo[1][test]"
		const segments = repeaterName.split(NameSeparator);
		const htmlName = segments[0] + segments.slice(1).map(seg => `[${seg}]`).join('');

		clone.querySelectorAll('[name]').forEach(el =>
			el.setAttribute('name', htmlName + el.getAttribute('name'))
		);

		['id', 'for'].forEach(attr => {
			clone.querySelectorAll(`[${attr}]`).forEach(el => {
				let value = el.getAttribute(attr);
				if (value.startsWith(IdPrefix)) {
					el.setAttribute(attr, value.replace(IdPrefix, IdPrefix + repeaterName));
				}
			});
		});

		clone.querySelectorAll('[data-nette-repeater]').forEach(cont =>
			cont.dataset.netteRepeater = repeaterName + NameSeparator + cont.dataset.netteRepeater
		);

		// Wrap in div with index
		const wrapper = document.createElement('div');
		wrapper.dataset.repeaterIndex = newIndex;
		wrapper.appendChild(clone);

		return wrapper;
	}
}


// Auto-initialize on DOM ready
if (typeof window !== 'undefined') {
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => new RepeaterManager());
	} else {
		new RepeaterManager();
	}
}

// Export for testing
if (typeof module !== 'undefined' && module.exports) {
	module.exports = RepeaterManager;
}
