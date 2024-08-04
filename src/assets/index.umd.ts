import { FormValidator } from './formValidator';
import { webalize } from './webalize';
import { version } from './package.json';

type NetteForms = FormValidator & { version: string; webalize: typeof webalize };
let nette = new FormValidator as NetteForms;
nette.version = version;
nette.webalize = webalize;

export default nette;
