module.exports = function (config) {
	config.set({
		basePath: '',
		frameworks: ['jasmine'],
		browsers: ['ChromeHeadless'],
		files: [
			'../../src/assets/dist/nette-forms.umd.js',
			'spec/*Spec.js',
		],
		autoWatch: false,
		singleRun: true,
	});
};
