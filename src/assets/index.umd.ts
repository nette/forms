import { FormValidator } from './formValidator';
import { Repeater } from './repeater';
import { webalize } from './webalize';
import { version } from './dist/package.json';

class NetteForms extends FormValidator {
	version = version;
	webalize = webalize;
	repeater = new Repeater(this);


	initOnLoad(): void {
		super.initOnLoad();
		this.repeater.init();
	}
}

let nette = new NetteForms;

export default nette;
