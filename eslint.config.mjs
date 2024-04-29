import globals from 'globals';
import pluginJs from '@eslint/js';

export default [
	pluginJs.configs.recommended,
	{
		languageOptions: {
			ecmaVersion: 6,
			globals: {
				...globals.browser,
				'Tracy': 'writeable',
				'define': 'readable',
				'module': 'readable',
			},
		},
		ignores: ['**/*.min.js'],
		rules: {
			indent: ['error', 'tab'],
			quotes: ['error', 'single'],
			semi: ['error', 'always'],
			'func-style': ['error', 'expression'],
			'prefer-arrow-callback': ['error'],
			'arrow-parens': ['error'],
			'arrow-spacing': ['error'],
			'no-unused-vars': ['error', {
				'caughtErrors': 'none',
			}],
		},
	},
];
