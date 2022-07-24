module.exports = function (grunt) {
	'use strict';

	// Load all grunt tasks matching the `grunt-*` pattern
	require('load-grunt-tasks')(grunt);

	// Show elapsed time
	require('@lodder/time-grunt')(grunt);

	// Project configuration
	grunt.initConfig(
		{
			package: grunt.file.readJSON('package.json'),
			// Replace related details from package.json
			replace: {
				dist: {
					src: ['<%= package.name %>.php', 'composer.json', 'includes/class-plugin.php'],
					overwrite: true,
					replacements: [
						{
							from: /Plugin(\s*)Name(\s*):(\s*)(.*)/,
							to: "Plugin$1Name$2:$3<%= package.title %>"
						},
						{
							from: /Description(\s*):(\s*)(.*)/,
							to: "Description$1:$2<%= package.description %>"
						},
						{
							from: /Version(\s*):(\s*)(.*)/,
							to: "Version$1:$2<%= package.version %>"
						},
						{
							from: /"version"(\s*):(\s*)(.*)/,
							to: '"version"$1:$2"<%= package.version %>",'
						},
						{
							from: /(\s*)\$version(\s*)=(\s*)(.*)/,
							to: "$1$version$2=$3'<%= package.version %>';"
						},
						{
							from: /Plugin(\s*)URI(\s*):(\s*)(.*)/,
							to: "Plugin$1URI$2:$3<%= package.homepage %>"
						},
						{
							from: /@link(\s*)(.*)/,
							to: "@link$1<%= package.homepage %>"
						},
						{
							from: /"homepage"(\s*):(\s*)(.*)/,
							to: '"homepage"$1:$2"<%= package.homepage %>",'
						}
					]
				}
			},
			checktextdomain: {
				options: {
					text_domain: "<%= package.name %>",
					fix:true,
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
						exclude: ['packages/*', '.git/*', 'node_modules/*', 'tests/*'],
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

	grunt.registerTask('i18n', ['checktextdomain', 'makepot']);
	grunt.registerTask('build', ['i18n', 'replace']);
}
