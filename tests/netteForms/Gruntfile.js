module.exports = function(grunt) {
	grunt.initConfig({
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			all: [
				'../../src/assets/*.js', '!../../src/assets/*.min.js'
			]
		},
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

	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-jasmine');

	grunt.registerTask('test', [
		'jshint','jasmine'
	]);
};
