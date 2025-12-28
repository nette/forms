import fs from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import vm from 'vm';

const __dirname = dirname(fileURLToPath(import.meta.url));


// Load and execute netteForms.js in global context
const netteFormsPath = join(__dirname, '../../src/assets/netteForms.js');
const netteFormsCode = fs.readFileSync(netteFormsPath, 'utf-8');
vm.runInThisContext(netteFormsCode);

// Load and execute repeater.js in global context
const repeaterPath = join(__dirname, '../../src/assets/repeater.js');
const repeaterCode = fs.readFileSync(repeaterPath, 'utf-8');
vm.runInThisContext(repeaterCode);

// Fix jsdom select element behavior to match browser
// In browsers, the first option is selected by default if no selected attribute is set
const originalInnerHTMLSetter = Object.getOwnPropertyDescriptor(Element.prototype, 'innerHTML').set;
Object.defineProperty(Element.prototype, 'innerHTML', {
	set: function(value) {
		originalInnerHTMLSetter.call(this, value);
		const selects = this.querySelectorAll('select');
		selects.forEach(select => {
			if (!select.hasAttribute('multiple') && select.options.length > 0 && select.selectedIndex === -1) {
				select.selectedIndex = 0;
			}
		});
		// Reset all form fields to their default values to fix jsdom's defaultValue tracking
		const inputs = this.querySelectorAll('input, textarea');
		inputs.forEach(input => {
			if (input.type === 'checkbox' || input.type === 'radio') {
				input.defaultChecked = input.hasAttribute('checked');
				input.checked = input.defaultChecked;
			} else {
				const attrValue = input.getAttribute('value');
				input.defaultValue = attrValue !== null ? attrValue : '';
				if (input.value === '') {
					input.value = input.defaultValue;
				}
			}
		});
	},
	configurable: true
});
