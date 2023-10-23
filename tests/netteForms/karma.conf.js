module.exports = function(config) {
	config.set({
		basePath: '',
		frameworks: ['jasmine'],
		browsers: ['ChromeHeadless'],
		files: [
			'node_modules/js-fixtures/fixtures.js',
			'../../src/assets/netteForms.js',
			'spec/*Spec.js'
		],
		autoWatch: false,
		singleRun: true,
	})
}
