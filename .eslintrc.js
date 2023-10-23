module.exports = {
	'env': {
		'browser': true,
		'amd': true,
		'commonjs': true
	},
	'globals': {
		'Tracy': true
	},
	'extends': 'eslint:recommended',
	'rules': {
		'indent': ['error', 'tab'],
		'quotes': ['error', 'single'],
		'semi': ['error', 'always'],
		'func-style': ['error', 'expression']
	}
};
