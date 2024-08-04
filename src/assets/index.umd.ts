// @ts-nocheck
import { FormValidator } from './formValidator';
import { webalize } from './webalize';
import { version } from './package.json';

let nette = new FormValidator;
nette.version = version;
nette.webalize = webalize;

export default nette;
