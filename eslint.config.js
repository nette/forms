import globals from 'globals';
import nette from '@nette/eslint-plugin/typescript';
import { defineConfig } from 'eslint/config';

export default defineConfig([
	{
		ignores: [
			'vendor', 'temp',
		],
	},

	{
		files: [
			'*.ts',
			'src/assets/*.ts',
		],

		languageOptions: {
			globals: {
				...globals.jasmine,
				...globals.amd,
				...globals.commonjs,
				Nette: 'readable',
				Tracy: 'writeable',
			},
		},

		extends: [
			nette.configs.typescript,
		],
	},
]);
