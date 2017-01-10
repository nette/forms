module.exports = function(grunt) {
	grunt.initConfig({
		jasmine: {
			test: {
				src: [
					'../../src/assets/*.js', '!../../src/assets/*.min.js'
				],
				options: {
					vendor: [
						'node_modules/js-fixtures/fixtures.js'
					],
					specs: 'spec/*Spec.js'
				}
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-jasmine');

	grunt.registerTask('test', [
		'jasmine'
	]);
};
