import { FormValidator } from './formValidator';
import { Repeater } from './repeater';
import { version } from './dist/package.json';

class NetteForms extends FormValidator {
	version = version;
	repeater = new Repeater(this);


	initOnLoad(): void {
		super.initOnLoad();
		this.repeater.init();
	}
}

let nette = new NetteForms;

export default nette;
