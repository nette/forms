import type { FormValidator } from './formValidator';
import type { Rule } from './types';

const IdPrefix = 'frm-';
const NameSeparator = '-';

/**
 * Client side support for Nette\Forms\Repeater: adding and removing of repeated items.
 *
 * Expected markup (rendered by Repeater::render() / {repeater}):
 *   <div data-nette-repeater="path" data-nette-repeater-min="1" data-nette-repeater-max="5">
 *     <div data-repeater-index="0"> ... </div>
 *     <template> ... controls with relative names like [email] ... </template>
 *   </div>
 *   <button data-nette-repeater-add="path">
 *   <button data-nette-repeater-remove> (inside an item)
 */
export class Repeater {
	#validator?: FormValidator;
	#initialized = false;


	constructor(validator?: FormValidator) {
		this.#validator = validator;
	}


	/**
	 * Installs a delegated click listener, so items added later need no re-initialization.
	 */
	init(root: Document | HTMLElement = document): void {
		if (this.#initialized) {
			return;
		}
		this.#initialized = true;

		root.addEventListener('click', (e) => {
			let target = e.target as HTMLElement;
			let addButton = target.closest<HTMLElement>('[data-nette-repeater-add]');
			if (addButton) {
				this.add(addButton);
				return;
			}
			let removeButton = target.closest<HTMLElement>('[data-nette-repeater-remove]');
			if (removeButton) {
				this.remove(removeButton);
			}
		});
	}


	/**
	 * Adds a new item to the repeater targeted by the button.
	 */
	add(button: HTMLElement): HTMLElement | null {
		let path = button.getAttribute('data-nette-repeater-add')!;
		let scope = (button as HTMLButtonElement).form ?? document;
		let container = scope.querySelector<HTMLElement>(`[data-nette-repeater="${path}"]`);
		if (!container) {
			return null;
		}

		let template = container.querySelector<HTMLTemplateElement>(':scope > template');
		let items = this.#getItems(container);
		let max = parseInt(container.getAttribute('data-nette-repeater-max') ?? '', 10);
		if (!template || (!isNaN(max) && items.length >= max)) {
			return null;
		}

		let index = items.reduce((res, item) => Math.max(res, parseInt(item.getAttribute('data-repeater-index')!, 10) + 1), items.length);
		let item = this.#cloneTemplate(template, path, index);

		let event = new CustomEvent('repeater:add', {
			bubbles: true,
			cancelable: true,
			detail: { container, item, index },
		});
		if (!container.dispatchEvent(event)) {
			return null;
		}

		container.insertBefore(item, template);
		this.#reinitValidation(container);
		this.updateButtons(container);
		container.dispatchEvent(new CustomEvent('repeater:added', {
			bubbles: true,
			detail: { container, item, index },
		}));
		return item;
	}


	/**
	 * Removes the item the button belongs to.
	 */
	remove(button: HTMLElement): boolean {
		let item = button.closest<HTMLElement>('[data-repeater-index]');
		let container = item?.parentElement;
		if (!item || !container?.hasAttribute('data-nette-repeater')) {
			return false;
		}

		let min = parseInt(container.getAttribute('data-nette-repeater-min') ?? '', 10);
		if (!isNaN(min) && this.#getItems(container).length <= min) {
			return false;
		}

		let event = new CustomEvent('repeater:remove', {
			bubbles: true,
			cancelable: true,
			detail: { container, item, modified: this.hasModifiedFields(item) },
		});
		if (!container.dispatchEvent(event)) {
			return false;
		}

		item.remove();
		this.#reinitValidation(container);
		this.updateButtons(container);
		return true;
	}


	/**
	 * Enables or disables add/remove buttons according to the min/max bounds.
	 */
	updateButtons(container: HTMLElement): void {
		let count = this.#getItems(container).length;
		let min = parseInt(container.getAttribute('data-nette-repeater-min') ?? '', 10);
		let max = parseInt(container.getAttribute('data-nette-repeater-max') ?? '', 10);
		let path = container.getAttribute('data-nette-repeater')!;
		let scope = container.closest('form') ?? document;

		scope.querySelectorAll<HTMLButtonElement>(`[data-nette-repeater-add="${path}"]`)
			.forEach((button) => button.disabled = !isNaN(max) && count >= max);

		this.#getItems(container).forEach((item) => {
			item.querySelectorAll<HTMLButtonElement>('[data-nette-repeater-remove]').forEach((button) => {
				if (button.closest('[data-repeater-index]') === item) {
					button.disabled = !isNaN(min) && count <= min;
				}
			});
		});
	}


	/**
	 * Checks whether any field inside the element differs from its default value.
	 */
	hasModifiedFields(element: HTMLElement): boolean {
		for (let input of element.querySelectorAll<HTMLInputElement | HTMLTextAreaElement>('input, textarea')) {
			if (input instanceof HTMLInputElement && (input.type === 'checkbox' || input.type === 'radio')) {
				if (input.checked !== input.defaultChecked) {
					return true;
				}
			} else if (input.value !== input.defaultValue) {
				return true;
			}
		}

		for (let select of element.querySelectorAll('select')) {
			for (let option of select.options) {
				if (option.selected !== option.defaultSelected) {
					return true;
				}
			}
		}

		return false;
	}


	#getItems(container: HTMLElement): HTMLElement[] {
		return Array.from(container.querySelectorAll<HTMLElement>(':scope > [data-repeater-index]'));
	}


	/**
	 * Clones the template and rewrites relative names, ids and validation rules of the new item.
	 */
	#cloneTemplate(template: HTMLTemplateElement, path: string, index: number): HTMLElement {
		let clone = template.content.cloneNode(true) as DocumentFragment;
		let prefix = path + NameSeparator + index;

		// converts prefix from 'user-persons-1' to HTML name 'user[persons][1]'
		let segments = prefix.split(NameSeparator);
		let htmlName = segments[0] + segments.slice(1).map((segment) => `[${segment}]`).join('');

		clone.querySelectorAll('[name]').forEach((el) => {
			let name = el.getAttribute('name')!;
			if (name.startsWith('[')) { // absolute names are left untouched, mirroring the server side
				el.setAttribute('name', htmlName + name);
			}
		});

		for (let attr of ['id', 'for']) {
			clone.querySelectorAll(`[${attr}]`).forEach((el) => {
				let value = el.getAttribute(attr)!;
				if (value.startsWith(IdPrefix)) {
					el.setAttribute(attr, value.replace(IdPrefix, IdPrefix + prefix));
				}
			});
		}

		for (let attr of ['data-nette-repeater', 'data-nette-repeater-add']) {
			clone.querySelectorAll(`[${attr}]`).forEach((el) => {
				el.setAttribute(attr, prefix + NameSeparator + el.getAttribute(attr));
			});
		}

		clone.querySelectorAll('[data-nette-rules]').forEach((el) => {
			let rules = JSON.parse(el.getAttribute('data-nette-rules')!) as Rule[];
			this.#rewriteRules(rules, htmlName);
			el.setAttribute('data-nette-rules', JSON.stringify(rules));
		});

		// the wrapper element (e.g. <div> or <tr>) is part of the template rendered by the server;
		// a missing one is tolerated for hand-written templates
		let wrapper = Array.from(clone.children).find((el) => el.hasAttribute('data-repeater-index')) as HTMLElement | undefined;
		if (!wrapper) {
			wrapper = document.createElement('div');
			wrapper.appendChild(clone);
		}
		wrapper.setAttribute('data-repeater-index', String(index));
		return wrapper;
	}


	/**
	 * Prefixes relative control references (starting with '[') in validation rules.
	 */
	#rewriteRules(rules: Rule[], htmlName: string): void {
		let rewrite = (name: unknown) => typeof name === 'string' && name.startsWith('[') ? htmlName + name : name;

		for (let rule of rules) {
			if (rule.control) {
				rule.control = rewrite(rule.control) as string;
			}
			for (let arg of Array.isArray(rule.arg) ? rule.arg : [rule.arg]) {
				if (arg && typeof arg === 'object' && 'control' in arg) {
					arg.control = rewrite(arg.control);
				}
			}
			if (rule.rules) {
				this.#rewriteRules(rule.rules, htmlName);
			}
		}
	}


	#reinitValidation(container: HTMLElement): void {
		let form = container.closest('form');
		if (form && this.#validator) {
			this.#validator.toggleForm(form);
		}
	}
}
