import globals from 'globals';
import pluginJs from '@eslint/js';

export default [
	pluginJs.configs.recommended,
	{
		ignores: ['**/*.min.js'],
	},
	{
		languageOptions: {
			ecmaVersion: 2021,
			globals: {
				...globals.browser,
				'Tracy': 'writeable',
				'define': 'readable',
				'module': 'readable',
			},
		},
		rules: {
			indent: ['error', 'tab'],
			quotes: ['error', 'single'],
			semi: ['error', 'always'],
			'func-style': ['error', 'declaration', {'allowArrowFunctions': true}],
			'prefer-arrow-callback': ['error'],
			'arrow-parens': ['error'],
			'arrow-spacing': ['error'],
			'no-var': ['error'],
		},
	},
];
