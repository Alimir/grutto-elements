module.exports = function( grunt ) {

	'use strict';
    // load all grunt tasks matching the `grunt-*` pattern
    require('load-grunt-tasks')(grunt);
	require('time-grunt')(grunt);

	// Project configuration
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		// Meta definitions
		meta: {
			project:   "grutto-elements",
			version:   "<%= pkg.title || pkg.name %> - v<%= pkg.version %>",
			copyright: "<%= pkg.author.name %> <%= grunt.template.today('yyyy') %>",

			header: "/*\n" +
				" *  <%= meta.version %>\n" +
				" *  <%= pkg.homepage %>\n" +
				" *\n" +
				" *  <%= pkg.description %>\n" +
				" *\n" +
				" *  <%= meta.copyright %>" +
				" */\n",

			phpheader: "\n" +
				" * @package    <%= pkg.name %>\n"+
				" * @author     <%= pkg.author.name %> <%= grunt.template.today('yyyy') %>\n"+
				" * @link       <%= pkg.homepage %>",


			buildDir: "build",
			projectSubDir: '<%= meta.project %>',
			buildPath:     '<%= meta.buildDir %>/<%= meta.projectSubDir %>',
			installableZipFile: '<%= meta.project %>', // '<%= meta.project %>-installable'
			zipBuildPath: '<%= meta.buildDir %>/<%= meta.installableZipFile %>.zip'
		},

        clean: {
            build: [
                '<%= meta.buildPath %>', '<%= meta.zipBuildPath %>',
            ],
            version: [
                '<%= meta.buildDir %>/<%= meta.project %>-*.txt',
            ]
        },

        shell:{
            zipBuild: {
                command: 'cd build; zip -FSr -9 <%= meta.project %>.<%= pkg.version %>.zip <%= meta.project %> -x */\.*; cd ..;' // exclude dotfiles
            },
            createBuildFolder: {
                command: "mkdir -p <%= meta.buildPath %>"
            },
            zipDlPack: {
                command: 'cd <%= meta.buildDir %>; zip -FSr -9 <%= meta.project %>-download-package * -x /<%= meta.projectSubDir %>/* */\.*; cd ..;' // exclude dotfiles
            },
            cleanBuildDotFiles: {
                command: ' find <%= meta.buildDir %> -name ".DS_Store" -delete' // exclude dotfiles
            },
            createTextVersion:{
                command: 'echo \"<%= pkg.title %>\" latest version: <%= meta.version %> >> <%= meta.buildDir %>/<%= meta.project %>-<%= meta.version %>.txt'
            }
        },


        // deploy via rsync
        deploy: {
            options: {
                args: ["--verbose -zP --delete-after"], // z:compress while transfering data, P: display progress
                exclude: ['.git*', 'node_modules', '.sass-cache', 'Gruntfile.js', 'package.json', '_devDependencies',
                          'css/sass', 'css/sass-output/', 'js/src',
                          '**/*.scss', '*.log', 'README.md', 'config.rb', '.jshintrc', 'bower.json', '.ds_store',
                          'bower_components','build', 'contributors.txt', 'config.rb'
                ],
                recursive: true,
                syncDestIgnoreExcl: true
            },

            build: {
                options: {
                    src: "./",
                    dest: "<%= meta.buildPath %>"
                }
            },

            lite: {
                options: {
                    exclude: [
                        '.git*', 'node_modules', '.sass-cache', 'Gruntfile.js', 'package.json', '_devDependencies',
                        '**/*.scss', '*.log', 'README.md', 'config.rb', '.jshintrc', 'bower.json', 'package-lock.json',
                        'bin', 'build', 'tests', 'contributors.txt', 'config.rb', '.ds_store', 'composer.json',  'composer.lock',
                        'yarn.lock', 'phpunit.xml.dist', '**/exclude/', '**/excluded/', '*.sh', '**/sass/', '**/scss/', '**/sass-output/',
                        'js/libs/', 'js/src/', '**/*.psd'
                    ],
                    src: ['./'],
                    dest: "<%= meta.buildPath %>"
                }
            }
        }

    } );

    grunt.renameTask('rsync', 'deploy');

    // compress the product in one pack
    grunt.registerTask( 'pack'          , ['shell:zipBuild'] );
    grunt.registerTask( 'buildVersion'  , ['clean:version', 'shell:createTextVersion'] );

    // deploy the lite version in /build folder
    grunt.registerTask( 'beta'          , ['clean:build','deploy:lite', 'shell:cleanBuildDotFiles'] );
    // build the final lite version in /build folder and pack the product
    grunt.registerTask( 'build'         , ['beta', 'buildVersion', 'pack'] );


	grunt.util.linefeed = '\n';

};
