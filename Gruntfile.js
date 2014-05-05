module.exports = function(grunt) {

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    phpunit: {
      classes: {
          dir: 'test/tests'
      },
      options: {
          // bin: 'vendor/bin/phpunit',
          // bootstrap: 'test/phpunit/bootstrap.php',
          colors: true
      }
    },

    phpdocumentor: {
        options: {
            // phar : null,
            command   : 'list',
        },
        docs: {
          options: {
              directory : 'classes',
              target    : 'docs/api'
          }
        }

    }

  });

  grunt.loadNpmTasks('grunt-phpunit');

  grunt.loadNpmTasks('grunt-phpdocumentor');

  grunt.registerTask('docs', ['phpdocumentor']);

  grunt.registerTask('default', ['phpunit']);

};
