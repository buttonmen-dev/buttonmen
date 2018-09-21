module.exports = function(grunt) {
 
  // Project configuration.
  grunt.initConfig({
 
    // Read the package.json (optional)
    pkg: grunt.file.readJSON('package.json'),
 
    clean: [],

    jshint: {
      all: ['../../src/ui/js/*.js'],
      options: grunt.file.readJSON('.jshintrc')
    }

        // Metadata.
//        meta: {
//            basePath: './../../',
//            srcPath: './../../src/',
//            deployPath: './../../deploy/'
//        },
 
//        banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
//                '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
//                '* Copyright (c) <%= grunt.template.today("yyyy") %> ',
 
        // Task configuration.
//        concat: {
//            options: {
//                stripBanners: true
//            },
//            dist: {
//                src: ['<%= meta.srcPath %>scripts/fileone.js', '<%= meta.srcPath %>scripts/filetwo.js'],
//                dest: '<%= meta.deployPath %>scripts/app.js'
//            }
//        }
//        uglify: {
//            options: {
//                banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
//            },
//            build: {
//                src: 'src/<%= pkg.name %>.js',
//                dest: 'build/<%= pkg.name %>.min.js'
//            }
//        }
//        jshint: {
//            all: ['Gruntfile.js', 'src/**/*.js', 'test/src/**/*.js']
//        }
//        inlinelint: {
//           html: ['./../../src/**/*.html'],
//            options: {
//                jshintrc: '.jshintrc'
//            }
//        }
  });
    
 
  // These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-lint-inline'); 

  // Default task
  grunt.registerTask('default', ['jshint']);
  grunt.registerTask('circleci', ['jshint']);
};
