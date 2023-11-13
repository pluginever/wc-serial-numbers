module.exports = function( grunt ) {
	'use strict';

	// Load all grunt tasks matching the `grunt-*` pattern.
	require( 'load-grunt-tasks' )( grunt );

	// Show elapsed time.
	require( '@lodder/time-grunt' )( grunt );

	// Project configuration.
	grunt.initConfig(
		{
			package: grunt.file.readJSON( 'package.json' ),
			addtextdomain: {
				options: {
					expand: true,
					text_domain: 'wc-serial-numbers',
					updateDomains: [ 'framework-text-domain' ],
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
			checktextdomain: {
				options: {
					text_domain: 'wc-serial-numbers',
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
						'!packages/**',
						'!node_modules/**',
						'!tests/**',
						'!vendor/**',
					],
					expand: true,
				},
			},
			makepot: {
				target: {
					options: {
						domainPath: 'languages',
						exclude: [ 'packages/*', '.git/*', 'node_modules/*', 'tests/*', 'vendor/*' ],
						mainFile: '<%= package.name %>.php',
						potFilename: '<%= package.name %>.pot',
						potHeaders: {
							'report-msgid-bugs-to': '<%= package.homepage %>',
							'project-id-version': '<%= package.title %> <%= package.version %>',
							poedit: true,
							'x-poedit-keywordslist': true,
						},
						type: 'wp-plugin',
						updateTimestamp: false,
					},
				},
			},
			wp_readme_to_markdown: {
				your_target: {
					files: {
						'readme.md': 'readme.txt',
					},
				},
			},
		}
	);

	grunt.registerTask( 'i18n', [ 'addtextdomain', 'checktextdomain', 'makepot' ] );
	grunt.registerTask( 'build', [ 'i18n' ] );
};
