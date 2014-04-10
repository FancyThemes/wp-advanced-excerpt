<?php
/*
Plugin Name: Advanced Excerpt
Plugin URI: http://deliciousbrains.com/advanced-excerpt/
Description: Control the appearance of WordPress post excerpts
Version: 4.1.1
Author: Delicious Brains
Author URI: http://deliciousbrains.com/
*/

$GLOBALS['advanced_excerpt_version'] = '4.1.1';

load_plugin_textdomain( 'advanced-excerpt', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

require_once 'class/advanced-excerpt.php';
require_once 'functions/functions.php';

function advanced_excerpt_init() {
	global $advanced_excerpt;
	$advanced_excerpt = new Advanced_Excerpt( __FILE__ );
}
add_action( 'init', 'advanced_excerpt_init', 5 );
