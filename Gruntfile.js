/* jshint node:true */
/* global require */
module.exports = function (grunt) {
    'use strict';

    var loader = require( 'load-project-config' ),
        config = require( 'grunt-plugin-fleet' );
    config = config();
    config.files.php.push( '!inc/PhpFormBuilder.php' );
    config.files.php.push( '!mailin.php' );
    config.files.js.push( '!gutenberg/**/*.js' );
    config.files.js.push( '!webpack.config.js' );
	config.taskMap.faq_builder = 'grunt-helpscout-faq';
    loader( grunt, config ).init();
};