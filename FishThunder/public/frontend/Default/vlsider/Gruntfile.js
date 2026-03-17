module.exports = function(grunt){
    
    //自动载入任务模块.
    require("matchdep").filterDev("grunt-*").forEach(grunt.loadNpmTasks);

    // 项目配置
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        //合并.
        concat:
        {
           options: {
           
              banner: '/*!\n * <%= pkg.name %> - JS for Debug\n * @licence <%= pkg.name %> - v<%= pkg.version %> (<%= grunt.template.today("yyyy-mm-dd") %>)\n * http://56hm.com/ | Licence: MIT\n */\n'
           
           },
            
           release: {
                
               src: [
                 'src/js/**/*.js'
               ],
               
               dest : 'dist/js/jquery.marquee.js'

           }
        },

        //jsmin
        uglify:
        {
            options: {

                banner: '/*!\n * <%= pkg.name %> - JS compressed \n * @licence <%= pkg.name %> - v<%= pkg.version %> (<%= grunt.template.today("yyyy-mm-dd") %>)\n * http://56hm.com/ | Licence: MIT\n */\n',
                preserveComments: false,
                expand: true
            },

            release: {

              files: {

                    'dist/js/jquery.marquee.min.js': ['<%= concat.release.dest %>']

              }

            }
         },



        watch:
        {
           less:
           {  
              files: ['src/less/**/*.less'],
              tasks:['less:dev', 'less:prod']
           },

           js:
           {
              files: ['src/js/**/*.js'],
              tasks:['concat:release', 'uglify:release']
           },
            
        }

    });


    // 默认任务
    grunt.registerTask('default', ['less:dev', 'less:prod' ,'concat:release','uglify:release']);
}