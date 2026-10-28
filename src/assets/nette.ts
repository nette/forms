import { FormValidator } from './formValidator';
import { version } from './dist/package.json';

export class NetteForms extends FormValidator {
	version = version;
}

export default new NetteForms;
