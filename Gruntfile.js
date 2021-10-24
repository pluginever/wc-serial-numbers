module.exports = function ( grunt ) {
	'use strict';
	// Project configuration
	grunt.initConfig( {
		addtextdomain: {
			options: {
				textdomain: 'wc-serial-numbers',
			},
			update_all_domains: {
				options: {
					updateDomains: true,
				},
				src: [
					'*.php',
					'**/*.php',
					'!.git/**/*',
					'!bin/**/*',
					'!node_modules/**/*',
					'!tests/**/*',
				],
			},
		},

		// Check textdomain errors.
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
					'**/*.php', //Include all files
					'!node_modules/**', // Exclude node_modules/
					'!tests/**', // Exclude tests/
					'!vendor/**', // Exclude vendor/
					'!tmp/**', // Exclude tmp/
				],
				expand: true,
			},
		},

		makepot: {
			target: {
				options: {
					domainPath: 'i18n/languages',
					exclude: [ '.git/*', 'bin/*', 'node_modules/*', 'tests/*' ],
					mainFile: 'wc-minmax-quantities.php',
					potFilename: 'wc-minmax-quantities.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true,
					},
					type: 'wp-plugin',
					updateTimestamp: true,
				},
			},
		},

		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt',
				},
			},
		},
	} );

	// Saves having to declare each dependency
	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	grunt.registerTask( 'default', [ 'i18n', 'readme' ] );
	grunt.registerTask( 'build', [ 'i18n', 'readme' ] );
	grunt.registerTask( 'i18n', [
		'addtextdomain',
		'checktextdomain',
		'makepot',
	] );
	grunt.registerTask( 'readme', [ 'wp_readme_to_markdown' ] );
	grunt.util.linefeed = '\n';
};
