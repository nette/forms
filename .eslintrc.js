module.exports = {
	'env': {
		'browser': true,
		'amd': true,
		'commonjs': true
	},
	'parserOptions': {
		'ecmaVersion': 6
	},
	'globals': {
		'Tracy': true
	},
	'extends': 'eslint:recommended',
	'rules': {
		'indent': ['error', 'tab'],
		'quotes': ['error', 'single'],
		'semi': ['error', 'always'],
		'func-style': ['error', 'expression'],
		'prefer-arrow-callback': ['error'],
		'arrow-parens': ['error'],
		'arrow-spacing': ['error'],
		'no-var': ['error']
	}
};
