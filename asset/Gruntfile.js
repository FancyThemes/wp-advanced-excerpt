module.exports = function(grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			build: {
				files: {
					'js/advanced-excerpt.min.js': 'js/advanced-excerpt.js'
				}
			}
		},
		compass: {
			dist: {
				options: {
				}
			}
		},
		watch: {
			js: {
				files: ['js/*'],
				tasks: ['uglify']
			},
			sass: {
				files: ['sass/*'],
				tasks: ['compass']
			},
			livereload: {
				files: ['js/*.min.js','css/*'],
				options: { livereload: true }
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-compass');

	grunt.registerTask('default', ['uglify','compass']);

};
