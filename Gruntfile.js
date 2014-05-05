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

  });

  grunt.loadNpmTasks('grunt-phpunit');

  grunt.registerTask('default', ['phpunit']);

};
