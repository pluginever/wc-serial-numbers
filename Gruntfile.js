/* jshint node:true */
module.exports = function (grunt) {
	'use strict';
	// Load multiple grunt tasks using globbing patterns
	require('load-grunt-tasks')(grunt);
	var sass = require('node-sass');

	grunt.initConfig({

		// Setting folder templates.
		dirs: {
			css: 'assets/css',
			fonts: 'assets/fonts',
			images: 'assets/images',
			js: 'assets/js'
		},

		// JavaScript linting with JSHint.
		jshint: {
			options: {
				jshintrc: '.jshintrc'
			},
			all: [
				'Gruntfile.js',
				'<%= dirs.js %>/*.js',
				'!<%= dirs.js %>/*.min.js'
			]
		},

		// Minify .js files.
		uglify: {
			options: {
				ie8: true,
				parse: {
					strict: false
				},
				output: {
					comments: /@license|@preserve|^!/
				}
			},
			dist: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= dirs.js %>/',
					ext: '.min.js'
				}]
			},
			vendor: {
				files: {
					// '<%= dirs.js %>/file.min.js': ['<%= dirs.js %>/file.js'],
				}
			}
		},

		// Compile all .scss files.
		sass: {
			options: {
				implementation: sass,
				sourceMap: false,
				outputStyle: 'compressed'
			},
			dist: {
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>/',
					src: ['*.scss'],
					dest: '<%= dirs.css %>/',
					ext: '.css'
				}]
			}
		},

		// Autoprefixer.
		postcss: {
			options: {
				processors: [
					require('autoprefixer')()
				]
			},
			dist: {
				src: [
					'<%= dirs.css %>/*.css'
				]
			}
		},

		// Minify all .css files.
		cssmin: {
			minify: {
				expand: true,
				cwd: '<%= dirs.css %>/',
				src: ['*.css'],
				dest: '<%= dirs.css %>/',
				ext: '.css'
			}
		},

		// Concatenate files.
		concat: {
			admin: {
				files: {
					// '<%= dirs.css %>/admin.css' : ['<%= dirs.css %>/select2.css', '<%= dirs.css %>/admin.css'],
					// '<%= dirs.css %>/admin-rtl.css' : ['<%= dirs.css %>/select2.css', '<%= dirs.css %>/admin-rtl.css']
				}
			}
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
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: [
					'**/*.php',               // Include all files
					'!node_modules/**',       // Exclude node_modules/
					'!tests/**',              // Exclude tests/
					'!vendor/**',             // Exclude vendor/
					'!tmp/**'                 // Exclude tmp/
				],
				expand: true
			}
		},

		// Generate POT files.
		makepot: {
			options: {
				type: 'wp-plugin',
				domainPath: 'i18n/languages',
				potHeaders: {
					'language-team': 'LANGUAGE <EMAIL@ADDRESS>'
				}
			},
			dist: {
				options: {
					potFilename: 'wc-serial-numbers.pot',
					exclude: [
						'includes/class-updater.php',
						'vendor/.*',
						'tests/.*',
						'tmp/.*'
					]
				}
			}
		},

		// Watch changes for assets.
		watch: {
			css: {
				files: ['<%= dirs.css %>/**/*.scss'],
				tasks: ['sass', 'postcss', 'cssmin', 'concat']
			},
			js: {
				files: [
					'<%= dirs.js %>/*js',
					'<%= dirs.js %>/*js',
					'!<%= dirs.js %>/*.min.js'
				],
				tasks: ['jshint', 'uglify']
			}
		}

	});

	// Register tasks.
	grunt.registerTask('build', ['jshint', 'uglify', 'sass', 'postcss', 'cssmin', 'concat', 'checktextdomain', 'makepot']);
};
