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

if ( version_compare( PHP_VERSION, '5.2', '<' ) ) {
	// Thanks for this Yoast!
	if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
		require_once ABSPATH.'/wp-admin/includes/plugin.php';
		deactivate_plugins( __FILE__ );
		wp_die( __( 'Advanced Excerpt requires PHP 5.2 or higher, as does WordPress 3.2 and higher. The plugin has now disabled itself.', 'advanced-excerpt' ) );
	}
}

require_once 'class/advanced-excerpt.php';
require_once 'functions/functions.php';

function advanced_excerpt_init() {
	global $advanced_excerpt;
	$advanced_excerpt = new Advanced_Excerpt( __FILE__ );
}
add_action( 'init', 'advanced_excerpt_init', 5 );
