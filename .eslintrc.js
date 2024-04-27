module.exports = {
	'env': {
		'browser': true,
		'amd': true,
		'commonjs': true
	},
	'parserOptions': {
		'ecmaVersion': 2021
	},
	'globals': {
		'Tracy': true
	},
	'extends': 'eslint:recommended',
	'rules': {
		'indent': ['error', 'tab'],
		'quotes': ['error', 'single'],
		'semi': ['error', 'always'],
		'func-style': ['error', 'declaration', {'allowArrowFunctions': true}],
		'prefer-arrow-callback': ['error'],
		'arrow-parens': ['error'],
		'arrow-spacing': ['error'],
		'no-var': ['error']
	}
};
