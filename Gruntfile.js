module.exports = function (grunt) {
	'use strict';

	// Load all grunt tasks matching the `grunt-*` pattern.
	require('load-grunt-tasks')(grunt);

	// Show elapsed time.
	require('@lodder/time-grunt')(grunt);

	// Project configuration.
	grunt.initConfig({
		addtextdomain: {
			options: {
				expand: true,
				text_domain: 'wc-serial-numbers',
				updateDomains: ['framework-text-domain'],
			},
			plugin: {
				files: {
					src: [
						'*.php',
						'**/*.php',
						'!node_modules/**',
						'!tests/**',
						'!vendor/**',
					],
				},
			},
		},
		makepot: {
			target: {
				options: {
					domainPath: 'languages',
					exclude: [
						'packages/*',
						'.git/*',
						'node_modules/*',
						'tests/*',
						'vendor/*',
					],
					mainFile: 'wc-serial-numbers.php',
					potFilename: 'wc-serial-numbers.pot',
					potHeaders: {
						'report-msgid-bugs-to':
							'https://pluginever.com/support',
						poedit: true,
						'x-poedit-keywordslist': true,
					},
					type: 'wp-plugin',
					updateTimestamp: false,
				},
			},
		},
	});

	grunt.registerTask('i18n', ['addtextdomain', 'makepot']);
	grunt.registerTask('build', ['i18n']);
};
