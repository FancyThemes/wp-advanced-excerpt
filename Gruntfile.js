module.exports = function(grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			build: {
				files: {
					'assets/js/advanced-excerpt.min.js': 'assets/js/advanced-excerpt.js'
				}
			}
		},
		sass: {
			compile: {
				files: [ {
					'assets/css/style.css': 'assets/sass/style.scss',
				} ]
			}
		},
		cssmin: {
			css: {
				src:  'assets/css/style.css',
				dest: 'assets/css/style.min.css'
			}
		},
		watch: {
			js: {
				files: ['assets/js/*'],
				tasks: ['uglify'],
			},
			sass: {
				files: ['assets/sass/*'],
			}
		},
		makepot: {
			options: {
				type: 'wp-plugin',
				domainPath: 'languages',
				potHeaders: {
					'report-msgid-bugs-to' : 'https://github.com/aprea/wp-advanced-excerpt/issues',
					'language-team'        : 'LANGUAGE <EMAIL@ADDRESS>'
				},
				exclude: [
					'node_modules/.*',  // Exclude node_modules/
				],
			},
			frontend: {
				options: {
					potFilename: 'advanced-excerpt.pot',
					processPot: function ( pot ) {
						pot.headers['project-id-version'];
						return pot;
					}
				}
			}
		},
	});

	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	grunt.registerTask( 'default', ['sass','cssmin','uglify'] );
	grunt.registerTask( 'dev', ['default','makepot'] );

};
