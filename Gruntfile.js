/* jshint node:true */
module.exports = function (grunt) {
	'use strict';
	var pkg = grunt.file.readJSON('package.json');

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

		// Sass linting with Stylelint.
		stylelint: {
			options: {
				configFile: '.stylelintrc'
			},
			all: [
				'<%= dirs.css %>/*.scss'
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
			admin: {
				files: [{
					expand: true,
					cwd: '<%= dirs.js %>/admin/',
					src: [
						'*.js',
						'!*.min.js'
					],
					dest: '<%= dirs.js %>/admin/',
					ext: '.min.js'
				}]
			},
			vendor: {
				files: {
					// '<%= dirs.js %>/file.min.js': ['<%= dirs.js %>/file.js'],
				}
			},
			frontend: {
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
			}
		},

		// Compile all .scss files.
		sass: {
			compile: {
				options: {
					sourceMap: 'none'
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.css %>/',
					src: ['*.scss'],
					dest: '<%= dirs.css %>/',
					ext: '.css'
				}]
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

		// Watch changes for assets.
		watch: {
			css: {
				files: ['<%= dirs.css %>/*.scss'],
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
						'apigen/.*',
						'vendor/.*',
						'tests/.*',
						'tmp/.*'
					]
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
					'!apigen/**',             // Exclude apigen/
					'!includes/libraries/**', // Exclude libraries/
					'!node_modules/**',       // Exclude node_modules/
					'!tests/**',              // Exclude tests/
					'!vendor/**',             // Exclude vendor/
					'!tmp/**'                 // Exclude tmp/
				],
				expand: true
			}
		},

		// PHP Code Sniffer.
		phpcs: {
			options: {
				bin: 'vendor/bin/phpcs'
			},
			dist: {
				src: [
					'**/*.php',                                                  // Include all files
					'!includes/libraries/**',                                    // Exclude libraries/
					'!node_modules/**',                                          // Exclude node_modules/
					'!tests/cli/**',                                             // Exclude tests/cli/
					'!tmp/**',                                                   // Exclude tmp/
					'!vendor/**'                                                 // Exclude vendor/
				]
			}
		},

		// Autoprefixer.
		postcss: {
			options: {
				processors: [
					require('autoprefixer')({
						browsers: [
							'> 0.1%',
							'ie 8',
							'ie 9'
						]
					})
				]
			},
			dist: {
				src: [
					'<%= dirs.css %>/*.css'
				]
			}
		},

		// Clean up build directory
		clean: {
			main: ['build/']
		},
		copy: {
			main: {
				src: [
					'**',
					'!node_modules/**',
					'!**/js/src/**',
					'!**/css/src/**',
					'!**/js/vendor/**',
					'!**/css/vendor/**',
					'!**/css/*.scss',
					'!**/images/src/**',
					'!**/sass/**',
					'!build/**',
					'!**/*.md',
					'!**/*.map',
					'!**/*.sh',
					'!.idea/**',
					'!bin/**',
					'!.git/**',
					'!Gruntfile.js',
					'!package.json',
					'!composer.json',
					'!composer.lock',
					'!package-lock.json',
					'!debug.log',
					'!none',
					'!.gitignore',
					'!.gitmodules',
					'!phpcs.xml.dist',
					'!npm-debug.log',
					'!plugin-deploy.sh',
					'!export.sh',
					'!config.codekit',
					'!nbproject/*',
					'!tests/**',
					'!.csscomb.json',
					'!.editorconfig',
					'!.jshintrc',
					'!.tmp'
				],
				dest: 'build/'
			}
		},

		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './build/'+pkg.name+'-v' + pkg.version + '.zip'
				},
				expand: true,
				cwd: 'build/',
				src: ['**/*'],
				dest: pkg.name
			}
		}

	});

	// Load NPM tasks to be used here.
	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-phpcs');
	grunt.loadNpmTasks('grunt-postcss');
	grunt.loadNpmTasks('grunt-stylelint');
	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-checktextdomain');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-prompt');

	// Register tasks.
	grunt.registerTask('default', [
		'js',
		'css',
		'i18n'
	]);

	grunt.registerTask('js', [
		'jshint',
		'uglify:admin',
		'uglify:frontend'
	]);

	grunt.registerTask('css', [
		'sass',
		'postcss',
		'cssmin',
		'concat'
	]);

	// Only an alias to 'default' task.
	grunt.registerTask('dev', [
		'default'
	]);

	grunt.registerTask('i18n', [
		'checktextdomain',
		'makepot'
	]);

	grunt.registerTask('release',
		[
			'default',
			'i18n'
		]);

	grunt.registerTask('build',
	[
		'clean',
		'clean',
		'copy'
	]);

	grunt.registerTask('zip',
		[
			'compress'
		]);
};
