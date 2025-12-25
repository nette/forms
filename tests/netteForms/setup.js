import fs from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import vm from 'vm';

const __dirname = dirname(fileURLToPath(import.meta.url));


// Load and execute netteForms.js in global context
const netteFormsPath = join(__dirname, '../../src/assets/netteForms.js');
const netteFormsCode = fs.readFileSync(netteFormsPath, 'utf-8');
vm.runInThisContext(netteFormsCode);
