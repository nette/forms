import globals from 'globals';
import pluginJs from '@eslint/js';
import stylistic from '@stylistic/eslint-plugin';
import tseslint from 'typescript-eslint';

export default [
	{
		ignores: [
			'*/', '!src/', '!tests/',
			'**/netteForms*.*',
		],
	},

	pluginJs.configs.recommended,
	...tseslint.configs.recommended,

	stylistic.configs.customize({
		indent: 'tab',
		braceStyle: '1tbs',
		arrowParens: true,
		semi: true,
		jsx: false,
	}),

	{
		languageOptions: {
			ecmaVersion: 'latest',
			globals: {
				...globals.browser,
				...globals.jasmine,
				...globals.amd,
				...globals.commonjs,
				Nette: 'readable',
				Tracy: 'writeable',
			},
		},
		plugins: {
			'@stylistic': stylistic,
		},
		rules: {
			'@stylistic/no-multiple-empty-lines': ['error', { max: 2, maxEOF: 0 }],
			'@stylistic/new-parens': ['error', 'never'],
			'@stylistic/padded-blocks': 'off',
			'@typescript-eslint/no-explicit-any': 'off',
			'func-style': ['error', 'declaration', { allowArrowFunctions: true }],
			'prefer-arrow-callback': 'error',
			'arrow-body-style': 'error',
			'eqeqeq': ['error', 'always', { null: 'ignore' }],
			'no-var': 'error',
			'prefer-const': 'off',
		},
	},
];
