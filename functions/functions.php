<?php

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