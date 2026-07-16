import { FormValidator } from './formValidator';
import { Repeater } from './repeater';
import { version } from './dist/package.json';

export class NetteForms extends FormValidator {
	version = version;
	repeater = new Repeater(this);


	initOnLoad(): void {
		super.initOnLoad();
		this.repeater.init();
	}
}

export default new NetteForms;
