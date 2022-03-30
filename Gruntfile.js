module.exports = function (grunt) {
	'use strict';
	// Project configuration
	grunt.initConfig({
		// Check textdomain errors.
		checktextdomain: {
			options: {
				text_domain: 'wc-serial-numbers',
				report_missing: true,
				correct_domain: true,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d',
				],
			},
			files: {
				src: [
					'**/*.php',
					'!node_modules/**',
					'!tests/**',
					'!vendor/**',
					'!bin/**',
				],
				expand: true,
			},
		},

		makepot: {
			target: {
				options: {
					domainPath: 'i18n/languages',
					exclude: ['.git/*', 'bin/*', 'node_modules/*', 'tests/*'],
					mainFile: 'wc-serial-numbers.php',
					potFilename: 'wc-serial-numbers.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true,
					},
					type: 'wp-plugin',
					updateTimestamp: true,
				},
			},
		},

		// Verify build
		shell: {
			command: [
				'rm -rf @next',
				'npm install',
				'npm run build',
				'rsync -rc --exclude-from="./.distignore" "." "./@next/" --delete --delete-excluded',
				'echo ',
				'echo === NOW COMPARE WITH ORG/GIT VERSION===',
			].join(' && '),
		},
	});

	// Saves having to declare each dependency
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

	// Register tasks.
	grunt.registerTask('default', ['build']);
	grunt.registerTask('build', ['checktextdomain', 'makepot']);
	grunt.registerTask('release', ['shell']);
	grunt.util.linefeed = '\n';
};
