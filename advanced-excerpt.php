<?php
/*
Plugin Name: Advanced Excerpt
Plugin URI: http://basvd.com/code/advanced-excerpt/
Description: Several improvements over WP's default excerpt. The size of the excerpt can be limited using character or word count, and HTML markup is not removed.
Version: 4.1.1
Author: Bas van Doren
Author URI: http://basvd.com/

Copyright 2007 Bas van Doren

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
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

function advanced_excerpt_init() {
	require_once 'class/advanced-excerpt.php';

	global $advanced_excerpt;
	$advanced_excerpt = new Advanced_Excerpt( __FILE__ );
}
add_action( 'init', 'advanced_excerpt_init', 5 );

// Do not use outside the Loop!
function the_advanced_excerpt( $args = '', $get = false ) {
	global $advanced_excerpt;
	if ( !empty( $args ) && !is_array( $args ) ) {
		$args = wp_parse_args( $args );

		// Parse query style parameters
		if ( isset( $args['ellipsis'] ) )
			$args['ellipsis'] = urldecode( $args['ellipsis'] );

		if ( isset( $args['allowed_tags'] ) )
			$args['allowed_tags'] = preg_split( '/[\s,]+/', $args['allowed_tags'] );

		if ( isset( $args['exclude_tags'] ) ) {
			$args['exclude_tags'] = preg_split( '/[\s,]+/', $args['exclude_tags'] );
		}
	}
	// Set temporary options
	$advanced_excerpt->options = $args;

	if ( $get )
		return get_the_excerpt();
	else
		the_excerpt();
}
