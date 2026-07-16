import type { FormValidator } from './formValidator';

/**
 * Client side support for Nette\Forms\Repeater: adding and removing of repeated items.
 *
 * Expected markup (rendered by Repeater::render() / {repeater}):
 *   <div data-nette-repeater="path" data-nette-repeater-min="1" data-nette-repeater-max="5">
 *     <div data-repeater-index="0"> ... </div>
 *     <template>
 *       <div data-repeater-index="__index0__"> ... controls like name="path[__index0__][email]" ... </div>
 *     </template>
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
		let item = this.#cloneTemplate(template, index);

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
	 * Clones the item wrapper from the template. Its data-repeater-index holds the placeholder the server
	 * used in place of the index, which is replaced in all attributes of the item, including nested templates.
	 */
	#cloneTemplate(template: HTMLTemplateElement, index: number): HTMLElement {
		let clone = template.content.cloneNode(true) as DocumentFragment;
		let wrapper = Array.from(clone.children).find((el) => el.hasAttribute('data-repeater-index'));
		let placeholder = wrapper?.getAttribute('data-repeater-index');
		if (!wrapper || !placeholder) {
			throw new Error('Repeater template must contain an item element with the data-repeater-index placeholder.');
		}

		this.#replacePlaceholder(clone, placeholder, String(index));
		// the HTML parser moves e.g. a <datalist> out of a table row, it still belongs to the item
		for (let el of Array.from(clone.children)) {
			if (el !== wrapper) {
				wrapper.appendChild(el);
			}
		}
		return wrapper as HTMLElement;
	}


	#replacePlaceholder(root: DocumentFragment, placeholder: string, index: string): void {
		for (let el of root.querySelectorAll('*')) {
			for (let attr of el.attributes) {
				if (attr.value.includes(placeholder)) {
					attr.value = attr.value.replaceAll(placeholder, index);
				}
			}
			if (el instanceof HTMLTemplateElement) {
				this.#replacePlaceholder(el.content, placeholder, index);
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
