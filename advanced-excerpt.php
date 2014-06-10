<?php
/*
Plugin Name: Advanced Excerpt
Plugin URI: http://wordpress.org/plugins/advanced-excerpt/
Description: Control the appearance of WordPress post excerpts
Version: 4.2.3
Author: Delicious Brains
Author URI: http://deliciousbrains.com/
*/

$GLOBALS['advanced_excerpt_version'] = '4.2.3';

function advanced_excerpt_load_textdomain() {
	load_plugin_textdomain( 'advanced-excerpt', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'wp_loaded', 'advanced_excerpt_load_textdomain' );

require_once 'class/advanced-excerpt.php';
require_once 'functions/functions.php';

function advanced_excerpt_init() {
	global $advanced_excerpt;
	$advanced_excerpt = new Advanced_Excerpt( __FILE__ );
}
add_action( 'init', 'advanced_excerpt_init', 5 );
