import { FormValidator } from './formValidator';
import { webalize } from './webalize';
import { version } from './dist/package.json';

export class NetteForms extends FormValidator {
	version = version;
	webalize = webalize;
}

export default new NetteForms;
