module.exports = function(grunt) {
	grunt.initConfig({
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			all: [
				'../../src/assets/*.js'
			]
		},
		jasmine: {
			test: {
				src: '../../src/assets/*.js',
				options: {
					vendor: [
						'bower_components/fixtures/fixtures.js'
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
